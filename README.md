# ewebtechs

This repository contains the cleaned site files.

## How to push your Hostinger site

1. Create a `.gitignore` with these rules:

   ```
   # Ignore WordPress backup archives and directories
   *.wpress
   public_html/**/ai1wm-backups/

   # Ignore thetripdealers and learn directories
   public_html/thetripdealers/
   public_html/learn/

   # Ignore Elementor generated styles
   public_html/**/uploads/elementor/css/
   ```

2. If backup files were already committed, remove them from history:

   ```bash
   git filter-branch --force --index-filter \
     'git rm --cached --ignore-unmatch -r \
        public_html/wp-content/ai1wm-backups \
        public_html/thetripdealers/wp-content/ai1wm-backups' \
     --prune-empty --tag-name-filter cat -- --all
   git gc --prune=now --aggressive
   ```

3. Finally push the cleaned repository:

   ```bash
   git push --force origin main
   ```

