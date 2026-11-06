# Courses page import

Moves the Courses page content from staging to another WordPress site (for example live).
It needs ACF Pro, the "Page Section" ACF field group (with the Section spacing field), and the theme deployed (the card images are in `wp-content/themes/harman/assets/images/cards/`).

Files: `courses-page-import.php` (WP-CLI script) and `courses-page.json` (the content: 4 sections, 7 cards, buttons, spacing, image names).

1. Dry run (changes nothing):  `wp eval-file courses-page-import.php --user=<admin login>`
2. Apply:                       `FWH_APPLY=1 wp eval-file courses-page-import.php --user=<admin login>`
3. Undo:                        `FWH_RESTORE=<backup file printed in step 2> wp eval-file courses-page-import.php --user=<admin login>`

The script saves a backup of the existing page sections (in the home folder) before it replaces them, adds any missing card images to the Media Library, and checks the saved page against the content file.
Run these files from your home folder, not from the web folder.
