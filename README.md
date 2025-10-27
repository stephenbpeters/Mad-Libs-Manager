# Mad Libs Manager 🎉

A lightweight, fun, and fully interactive **Mad Libs generator plugin** for WordPress.  
Create story templates with placeholders, let visitors fill them in, and instantly display their hilarious results — all saved automatically!

This plugin is based on the [classic Mad Libs game.](https://en.wikipedia.org/wiki/Mad_Libs)

---

## ✨ Features

- 🧩 Create unlimited Mad Libs style templates.
- 🗒️ Use placeholders like `{{noun}}`, `{{verb:past}}`, `{{adjective}}`, etc.
- 🪄 Smart label formatting (`persons_name` → `Persons name`).
- ⚡ Inline AJAX story display (no page reload).
- 💾 Each submission saved as its own entry.
- 📄 Dedicated “Mad Libs Submissions” page.
- 🔍 Public search and pagination for submissions.
- 🛠️ Admin tools for bulk delete, filtering, and search.
- 🔒 Safe input sanitization and apostrophe handling (v3.8+).

---

## 🚀 Quick Start

1. Download or clone the repo:
   ```bash
   git clone https://github.com/yourusername/mad-libs-manager.git
   ```
2. Zip the folder or install it directly in your WordPress `wp-content/plugins/` directory.
3. Activate it from your WordPress dashboard.
4. Go to **Mad Libs → Add New** to create a story.
5. Use the shortcode:
   ```shortcode
   [madlibs id=123]
   ```
6. A page titled **“Mad Libs Submissions”** will be created automatically — it lists all user-generated stories.

---

## 🧠 Example Template

```
One day, I saw a {{adjective}} {{animal}} {{verb:past}} down the street.
It was carrying a {{object}} full of {{liquid}}!
```

Visitors will see fields for each placeholder, fill them in, and instantly see their story — which will also be saved to the submissions list.

---

## ⚙️ Development

- Written in pure PHP and jQuery.
- Compatible with WordPress 5.0+ and tested on 6.8.3.
- Uses `wp_ajax` for secure form handling.
- All data sanitized using WordPress standards.

---

## 🧾 Changelog

**v3.8**
- Fixed apostrophe escaping (`\'`) issue.
- Improved text sanitization for new submissions.

**v3.7**
- Dedicated “Mad Libs Submissions” page.
- Correct filtering per template.

**v3.6**
- Label cleanup, heading placement, and style tweaks.

**v3.5**
- Added “View previous submissions →” link.

…and much more — see `readme.txt` for full history.

---

## 📜 License

The code is licensed under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).  
You’re free to use, modify, and redistribute this plugin as long as you retain the license.

MAD LIBS Trademark of PENGUIN GROUP (USA) INC and are [available for purchase here: http://madlibs.com](http://madlibs.com)

---

## ❤️ Credits

Developed by **ChatGPT (GPT-5)** with prompting by Stephen Peters.  
Inspired by classic word games — powered by modern WordPress.
