# Moving On Up — IT202 Project 2 (Fall 2026)

This is an integrated PHP + MySQL application based on the original Project 1 representative login. **Not deployed or verified against an NJIT database yet.** Do not claim the site is working until you complete the setup and tests.

## Requirements covered
- 5 normalized MySQL tables, 10 representative records and 10 sample clients with personal info, a move and instructions for each.
- Database-backed representative login, validated JavaScript and server-side input, transaction menu and navigation links.
- SQL JOIN search scoped to logged-in representative; client verification and client account creation + personal information; moving event booking with generated Moving ID; cancellation with cascading removal of instructions; creating and appending instructions.
- Custom CSS and JS, no CSS framework.
- 5 CSV files supplied as initial sample exports. **Re-export actual database tables after running and testing on NJIT** so the submitted CSVs match the real DB.

## Important setup before submitting
1. Open NJIT phpMyAdmin and select **your assigned existing database**.
2. Import `database/project2_schema_and_seed.sql` (this resets 5 tables with the named schema; do not re-import after adding real records). If table creation is restricted, contact course support.
3. Edit `config.php` with your real NJIT MySQL hostname, database name, username and password. `localhost` is only a placeholder; use the host stated in your NJIT course account setup.
4. Upload folder `Project_2` and its code files to your AFS `public_html` directory (PHP requires the server; do not just open index.php locally). If the professor requires direct `public_html` files, upload their contents there or keep this folder under `public_html` and provide its URL.
5. Visit `https://web.njit.edu/~YOUR_UCID/Project_2/index.php` (replace YOUR_UCID with yours; check the exact URL for your account).
6. Log in using one of the demo representative accounts below; check Search, Verify, Book, Instructions, Update, Cancel and Create Client.
7. In phpMyAdmin export **all five current tables as CSV** after testing, and replace the provided initial CSVs with those exports before submission.
8. Upload all required HTML/PHP/CSS/JS/CSV files and working website URL to Canvas, and push code and project URL to a dedicated GitHub Project 2 folder/repository. Do **not** push your real database password: replace `config.php` with placeholders before pushing, and keep actual credentials only on the AFS server.

## Demo login (sample data, not a real account)
First name Sarah; last name Johnson; password `!Move1`; ID `1001`; phone `7325550101`; extension `101`; email `sarah@mou.com` (only necessary when checkbox is checked). Choose Search from the dropdown.

## Important limitations
- This ZIP alone cannot create tables in your NJIT account, configure private MySQL credentials, publish to AFS, commit to your GitHub account, or submit to Canvas.
- The original `script.js` and `style-1.css` were provided as screenshots, so the functional JavaScript and styling in `assets/` are reconstructed rather than byte-for-byte copies. `original_project1.html` is the exact original HTML upload, included for reference.
- Browser `confirm()` is used for confirmations. A server still validates all writes. AFS/MySQL configuration and database privileges can differ.
- Current app demonstrates password_hash for the seeded data; sample passwords are public exercise credentials only. Do not use as actual personal passwords.
- Application source must be deployed on a PHP-enabled web server (not GitHub Pages).
