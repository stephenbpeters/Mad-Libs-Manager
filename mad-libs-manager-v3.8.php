<?php
/**
 * Plugin Name: Mad Libs Manager v3.8
 * Description: Preserves apostrophes and special characters in inline output for new submissions; keeps dedicated submissions page and all prior features.
 * Version: 3.8
 * Author: ChatGPT (GPT-5)
 * Text Domain: mad-libs-manager
 */

if (!defined('ABSPATH')) exit;

class MadLibs_Manager_v38 {

    const SUBMISSIONS_PAGE_TITLE = 'Mad Libs Submissions';
    const SUBMISSIONS_PAGE_OPTION = 'madlibs_submissions_page_id';

    public function __construct() {
        add_action('init', array($this, 'register_cpts'));
        add_action('add_meta_boxes', array($this, 'add_shortcode_metabox'));
        add_action('wp_enqueue_scripts', array($this, 'public_assets'));
        add_action('wp_ajax_madlibs_render', array($this, 'ajax_render'));
        add_action('wp_ajax_nopriv_madlibs_render', array($this, 'ajax_render'));
        add_action('wp_ajax_madlibs_search', array($this, 'ajax_search_entries'));
        add_action('wp_ajax_nopriv_madlibs_search', array($this, 'ajax_search_entries'));
        add_shortcode('madlibs', array($this, 'shortcode_render'));
        add_shortcode('madlibs_entries', array($this, 'shortcode_entries'));

        // Admin UI niceties for entries
        add_filter('manage_edit-madlibs_entry_columns', array($this, 'entry_columns'));
        add_action('manage_madlibs_entry_posts_custom_column', array($this, 'render_entry_column'), 10, 2);
        add_action('restrict_manage_posts', array($this, 'entries_admin_filter_by_template'));
        add_filter('parse_query', array($this, 'entries_admin_filter_parse_query'));
    }

    /*** Activation helpers ***/
    public static function activate() {
        $mgr = new self();
        $mgr->register_cpts();
        // Create the dedicated submissions page if it doesn't exist
        $page_id = get_option(self::SUBMISSIONS_PAGE_OPTION);
        if (!$page_id || get_post_status($page_id) !== 'publish') {
            // Try to find by title first
            $existing = get_page_by_title(self::SUBMISSIONS_PAGE_TITLE, OBJECT, 'page');
            if ($existing && $existing->post_status === 'publish') {
                $page_id = $existing->ID;
            } else {
                $page_id = wp_insert_post(array(
                    'post_type'   => 'page',
                    'post_status' => 'publish',
                    'post_title'  => self::SUBMISSIONS_PAGE_TITLE,
                    'post_content'=> '[madlibs_entries]',
                ));
            }
            if ($page_id && !is_wp_error($page_id)) {
                update_option(self::SUBMISSIONS_PAGE_OPTION, $page_id);
            }
        }
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    public function register_cpts() {
        register_post_type('madlibs_template', array(
            'labels' => array('name'=>'Mad Lib Templates','singular_name'=>'Mad Lib Template','menu_name'=>'Mad Libs'),
            'public'=>false,'show_ui'=>true,'show_in_menu'=>true,
            'menu_icon'=>'dashicons-edit','menu_position'=>25,'supports'=>array('title','editor')
        ));

        register_post_type('madlibs_entry', array(
            'labels'=>array('name'=>'Mad Lib Submissions','singular_name'=>'Mad Lib Submission'),
            'public'=>true,'has_archive'=>true,'rewrite'=>array('slug'=>'madlib'),
            'supports'=>array('title','editor','author'),
            'show_in_menu'=>'edit.php?post_type=madlibs_template',
            'capability_type'=>'post','map_meta_cap'=>true
        ));
    }

    public function add_shortcode_metabox() {
        add_meta_box('madlibs_shortcode_box','Shortcode',function($post){
            echo '<p>Embed this Mad Libs template with shortcode:</p>';
            echo '<input type="text" readonly style="width:100%" value="[madlibs id=' . esc_attr($post->ID) . ']" />';
        },'madlibs_template','side','high');
    }

    public function public_assets() {
        wp_enqueue_script('madlibs-public', plugin_dir_url(__FILE__) . 'madlibs-public.js', array('jquery'), false, true);
        wp_localize_script('madlibs-public', 'MadlibsAjax', array(
            'ajax_url'=>admin_url('admin-ajax.php'),
            'nonce'=>wp_create_nonce('madlibs_nonce')
        ));
        add_action('wp_footer', array($this, 'inline_assets'));
    }

    public function inline_assets() { ?>
        <style>
        .madlibs-wrapper{border:1px dashed #ccc;padding:15px;border-radius:8px;margin:15px 0;background:#fafafa;}
        .madlibs-wrapper label{font-weight:bold;display:block;margin-top:10px}
        .madlibs-wrapper input[type=text]{width:100%;box-sizing:border-box;padding:6px;margin-top:4px}
        .madlibs-form button{margin-top:10px;margin-right:10px;padding:6px 12px;border-radius:4px;cursor:pointer}
        .madlibs-result{margin-top:15px;background:#f9f9f9;padding:10px;border-radius:5px;display:none}
        .madlibs-entries-list form{margin-bottom:10px}
        .madlibs-entries-list input[type=search]{padding:6px;width:60%;max-width:300px}
        .madlibs-pagination a{margin-right:8px;text-decoration:none}
        .madlibs-entries-list h4{margin-top:1em;}
        </style>
        <script>
        (function($){
            $(document).on('submit', '.madlibs-form', function(e){
                e.preventDefault();
                var $form=$(this),$wrap=$form.closest('.madlibs-wrapper');
                var id=$wrap.data('template-id');
                var data={action:'madlibs_render',id:id,nonce:MadlibsAjax.nonce};
                $form.find('input[name]').each(function(){data[$(this).attr('name')]=$(this).val();});
                $wrap.find('.madlibs-result').hide().html('Loading...').fadeIn(200);
                $.post(MadlibsAjax.ajax_url,data,function(resp){
                    if(resp.success){
                        var html=resp.data.html;
                        if(resp.data.template_link){html+='<p><a href="'+resp.data.template_link+'" target="_blank">View previous submissions →</a></p>';}
                        $wrap.find('.madlibs-result').html(html).fadeIn(300);
                    }else{$wrap.find('.madlibs-result').html('<em>Error: '+(resp.data||'Unknown')+'</em>').fadeIn(200);}
                },'json');
            });

            $(document).on('click','.madlibs-reset',function(){
                var $wrap=$(this).closest('.madlibs-wrapper');
                $wrap.find('input[type=text]').val('');
                $wrap.find('.madlibs-result').hide().html('');
            });

            $(document).on('submit','.madlibs-search-form',function(e){
                e.preventDefault();
                var $form=$(this),query=$form.find('input[name=madlibs_q]').val();
                var container=$form.closest('.madlibs-entries-list');
                container.find('.madlibs-entries-results').html('Searching...');
                $.post(MadlibsAjax.ajax_url,{action:'madlibs_search',nonce:MadlibsAjax.nonce,q:query},function(resp){
                    if(resp.success){
                        container.find('.madlibs-entries-results').html(resp.data.html);
                    } else {
                        container.find('.madlibs-entries-results').html('<p><em>Error loading search results.</em></p>');
                    }
                },'json');
            });
        })(jQuery);
        </script>
    <?php }

    private function parse_fields($template) {
        preg_match_all('/\{\{\s*(.+?)\s*\}\}/',$template,$matches);
        $fields=array();
        foreach($matches[1] as $raw){
            $parts=preg_split('/[\|\:]/',$raw,2);
            $label=trim($parts[0]);
            $modifier=isset($parts[1])?trim($parts[1]):'';
            $name=sanitize_title($label.($modifier?'_'.$modifier:''));
            $fields[$name]=array('label'=>$label,'modifier'=>$modifier,'name'=>$name);
        }
        return $fields;
    }

    private function apply_modifier($word,$modifier){
        if(!$modifier)return $word;
        $mod=strtolower($modifier);
        if($mod==='plural'){
            if(preg_match('/(s|x|z|sh|ch)$/i',$word))return $word.'es';
            if(preg_match('/[^aeiou]y$/i',$word))return preg_replace('/y$/i','ies',$word);
            return $word.'s';
        }
        if($mod==='past'){
            if(preg_match('/e$/i',$word))return $word.'d';
            return $word.'ed';
        }
        return $word;
    }

    public function shortcode_render($atts){
        $atts=shortcode_atts(array('id'=>0),$atts,'madlibs');
        $id=intval($atts['id']);
        $post=get_post($id);
        if(!$post)return '<p><em>Template not found.</em></p>';
        $template=$post->post_content;
        $fields=$this->parse_fields($template);
        ob_start();?>
        <div class="madlibs-wrapper" data-template-id="<?php echo esc_attr($id); ?>">
            <form class="madlibs-form">
                <?php foreach($fields as $f): 
                    $label_clean = ucwords(str_replace(array('_','-'),' ',$f['label'])); ?>
                    <p><label><?php echo esc_html($label_clean); ?></label>
                    <input type="text" name="<?php echo esc_attr($f['name']); ?>" required /></p>
                <?php endforeach; ?>
                <p><button type="submit">Fill Story</button>
                <button type="button" class="madlibs-reset">Reset</button></p>
            </form>
            <div class="madlibs-result"></div>
        </div>
        <?php return ob_get_clean();
    }

    public function ajax_render(){
        check_ajax_referer('madlibs_nonce','nonce');
        $id=intval($_POST['id']);$post=get_post($id);
        if(!$post)wp_send_json_error('Template not found');
        $template=$post->post_content;
        $fields=$this->parse_fields($template);

        // Replace placeholders using UNSLASHED + sanitized inputs to avoid stray backslashes in output
        foreach($fields as $f){
            $raw = isset($_POST[$f['name']]) ? wp_unslash($_POST[$f['name']]) : '';
            $val = sanitize_text_field($raw);
            if($f['modifier']) $val = $this->apply_modifier($val,$f['modifier']);
            $pattern='/\{\{\s*'.preg_quote($f['label'],'/').'(?:\s*[\|\:]\s*[a-zA-Z0-9_-]+)?\s*\}\}/i';
            $template=preg_replace($pattern,$val,$template);
        }

        // Remove any leftovers
        $template=preg_replace('/\{\{[^}]+\}\}/','',$template);

        // Save entry (DB handles its own escaping), store a simple preformatted version
        $entry_id=wp_insert_post(array(
            'post_type'=>'madlibs_entry',
            'post_title'=>$post->post_title.' - '.current_time('F j, Y g:i a'),
            'post_content'=>wp_kses_post(nl2br(esc_html($template))), // safe & printable for new entries
            'post_status'=>'publish',
            'post_parent'=>$id
        ));

        // Link to dedicated submissions page with ?template=ID
        $page_id = intval(get_option(self::SUBMISSIONS_PAGE_OPTION));
        $entries_page = $page_id ? get_permalink($page_id) : get_post_type_archive_link('madlibs_entry');
        $template_link = add_query_arg('template', $id, $entries_page);

        // Return inline result preserving punctuation (apostrophes) safely
        $inline_html = nl2br(esc_html($template));

        wp_send_json_success(array('html'=>$inline_html,'template_link'=>$template_link));
    }

    public function shortcode_entries($atts){
        $atts=shortcode_atts(array('posts_per_page'=>10,'search'=>'true'),$atts,'madlibs_entries');
        $paged=isset($_GET['madpage'])?max(1,intval($_GET['madpage'])):1;
        $args=array(
            'post_type'=>'madlibs_entry','posts_per_page'=>intval($atts['posts_per_page']),
            'paged'=>$paged,'orderby'=>'date','order'=>'DESC'
        );
        if(isset($_GET['template'])){
            $args['post_parent']=intval($_GET['template']);
        }
        $query=new WP_Query($args);
        ob_start();
        echo '<div class="madlibs-entries-list">';
        if($atts['search']!=='false'){
            echo '<form class="madlibs-search-form"><input type="search" name="madlibs_q" placeholder="Search Mad Libs..."> <button type="submit">Search</button></form>';
        }
        if(isset($_GET['template'])){
            $template_id=intval($_GET['template']);
            $title=get_the_title($template_id);
            echo '<h4>All previous submissions for <em>'.esc_html($title).'</em>:</h4>';
        }
        echo '<div class="madlibs-entries-results">';
        if($query->have_posts()){
            echo '<ul>';
            while($query->have_posts()){ $query->the_post();
                echo '<li><a href="'.get_permalink().'">'.esc_html(get_the_title()).'</a> — '.esc_html(get_the_date()).'</li>';
            }
            echo '</ul>';
            echo '<div class="madlibs-pagination">';
            echo paginate_links(array('format'=>'?madpage=%#%','current'=>$paged,'total'=>$query->max_num_pages,'prev_text'=>'« Prev','next_text'=>'Next »'));
            echo '</div>';
        } else {
            echo '<p>No submissions found.</p>';
        }
        echo '</div></div>';
        wp_reset_postdata();
        return ob_get_clean();
    }

    public function ajax_search_entries(){
        check_ajax_referer('madlibs_nonce','nonce');
        $q=sanitize_text_field($_POST['q']??'');
        $args=array('post_type'=>'madlibs_entry','s'=>$q,'posts_per_page'=>10);
        $posts=get_posts($args);
        ob_start();
        if($q) echo '<h4>Search results for “'.esc_html($q).'”</h4>';
        if($posts){
            echo '<ul>';
            foreach($posts as $p){
                echo '<li><a href="'.get_permalink($p->ID).'">'.esc_html($p->post_title).'</a> — '.esc_html(get_the_date('', $p)).'</li>';
            }
            echo '</ul>';
        } else {
            echo '<p><em>No results found.</em></p>';
        }
        wp_send_json_success(array('html'=>ob_get_clean()));
    }

    /*** Admin columns & filters ***/
    public function entry_columns($cols){
        $cols_out=array();
        foreach($cols as $k=>$v){if($k==='date')continue;$cols_out[$k]=$v;}
        $cols_out['template']='Template';$cols_out['date']='Date';
        return $cols_out;
    }

    public function render_entry_column($column,$post_id){
        if($column==='template'){
            $parent=wp_get_post_parent_id($post_id);
            if($parent){
                echo '<a href="'.esc_url(get_edit_post_link($parent)).'">'.esc_html(get_the_title($parent)).'</a>';
            } else {echo '—';}
        }
    }

    public function entries_admin_filter_by_template(){
        global $typenow;
        if($typenow!=='madlibs_entry')return;
        $templates=get_posts(array('post_type'=>'madlibs_template','posts_per_page'=>-1,'orderby'=>'title','order'=>'ASC'));
        $current=isset($_GET['madlibs_parent'])?intval($_GET['madlibs_parent']):0;
        echo '<select name="madlibs_parent"><option value="0">All Templates</option>';
        foreach($templates as $t){
            printf('<option value="%d"%s>%s</option>', $t->ID, selected($current, $t->ID, false), esc_html($t->post_title));
        }
        echo '</select>';
    }

    public function entries_admin_filter_parse_query($query){
        global $pagenow;
        if(!is_admin()||$pagenow!=='edit.php')return;
        $post_type=isset($_GET['post_type'])?$_GET['post_type']:'';
        if($post_type!=='madlibs_entry')return;
        if(isset($_GET['madlibs_parent'])&&intval($_GET['madlibs_parent'])>0){
            $query->query_vars['post_parent']=intval($_GET['madlibs_parent']);
        }
    }
}

register_activation_hook(__FILE__, array('MadLibs_Manager_v38', 'activate'));
register_deactivation_hook(__FILE__, array('MadLibs_Manager_v38', 'deactivate'));

new MadLibs_Manager_v38();
