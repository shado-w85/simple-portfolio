## Hall 3 — Simple Portfolio (Upgraded)

This project is a responsive hostel website with a modern Bootstrap UI, cookie consent, and an optional secure PHP + MySQLi backend endpoint for the contact form.

### Features

- **Responsive UI**: Works on phones, tablets, laptops, desktops, and large screens
- **Modern layout & UX**: Sticky navbar, clear sections, accessible focus states
- **Performance**: Minimal custom CSS, deferred JS, CDN Bootstrap
- **Security hardening**: Safer defaults via meta policies + prepared statements (PHP)
- **Cookie consent**: Lightweight banner with accept/decline

### Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend (optional)**: PHP, MySQLi, MySQL

### How to Run (Static)

Open `index.html` in a browser.

### How to Run (PHP + MySQL)

- **Step 1**: Import `schema.sql` into your MySQL server.
- **Step 2**: Copy `config.php.example` to `config.php` and set DB credentials.
- **Step 3**: Serve the folder with PHP enabled (e.g., Apache/XAMPP).
- **Step 4**: Submit the form in `index.html#contact` → it will POST to `contact.php`.

### Author

Alex Audax
