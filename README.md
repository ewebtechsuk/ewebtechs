# eWeb Techs

This repository is prepared to hold the files for the `ewebtechs.com` site.

The `public_html` directory is tracked with a placeholder `.gitkeep` file so the folder exists in this repository even when no site files are present. If you already have a working WordPress installation at `/htdocs/eWeb\ Techs/public_html` on your local machine, copy those files into this repository's `public_html/` directory and commit the results:


1. Copy your website files into the `public_html/` directory.
2. Run `git add public_html` to stage them.
3. Commit the files: `git commit -m "Add site files"`.
4. Push to GitHub: `git push origin main` (use `--force` if overwriting).

## Import existing site

If your WordPress files already exist in `/htdocs/eWeb\ Techs/public_html`:

1. Copy everything from that directory into this repo's `public_html/`.
2. Ensure there is **no** nested `.git` folder inside `public_html/`.
   If one exists, remove it with `rm -rf public_html/.git`.
3. Run the commit steps above to add and push the files to GitHub.

## Troubleshooting push errors

If `git push` rejects your commit because the remote contains work you
don't have locally, fetch the remote history first:

```bash
git pull origin main --allow-unrelated-histories
```

Resolve any merge conflicts, commit the result, and push again. Use
`--force` only if you intend to overwrite the history on GitHub.

## Import from Hostinger

If your live WordPress site is hosted on Hostinger and you want to copy those
files here, you can archive them over SSH and download the archive:

```bash
ssh <user>@<host> "cd /home/<user>/public_html && tar -czf site.tar.gz ."
scp <user>@<host>:/home/<user>/public_html/site.tar.gz .
tar -xzf site.tar.gz -C public_html && rm site.tar.gz
```

After extracting the archive, ensure no `.git` folder was included and remove
any macOS `.DS_Store` files before committing:

```bash
find public_html -name '.DS_Store' -delete
rm -rf public_html/.git
```

Then follow the commit steps above.


This README was added from Codex environment which cannot connect to the
original server or GitHub, so you must perform the above steps on a machine
with network access.

## Verify updates on Hostinger

Because this environment cannot reach external networks, it cannot confirm
whether the files currently in `public_html/` are live on Hostinger. To check
from your own machine:

1. SSH into the Hostinger account using the credentials visible in the SSH
   Access panel (`82.219.189.219`, port `65002`, user `u758780474`):
   ```bash
   ssh -p 65002 u758780474@82.219.189.219
   ```
2. Navigate to the document root and list recently modified files to compare
   timestamps with your local copy:
   ```bash
   cd /home/u758780474/public_html
   find . -maxdepth 2 -type f -printf "%TY-%Tm-%Td %TH:%TM %p\n" | sort
   ```
3. Optionally download the file you expect to be updated and compare it with
   your local version:
   ```bash
   scp -P 65002 u758780474@82.219.189.219:/home/u758780474/public_html/path/to/file ./remote-file
   diff -u remote-file public_html/path/to/file
   ```
4. For a quick checksum comparison against multiple files at once, run the
   helper script in this repository (from your local machine with network
   access):
   ```bash
   ./scripts/compare-hostinger.sh path/to/file.php another/file.css
   ```
   The script uses MD5 hashes to report whether the remote and local versions
   match. Override `REMOTE_HOST`, `REMOTE_PORT`, `REMOTE_USER`, or
   `REMOTE_DIR` if your Hostinger settings change.

If the timestamps or file contents do not match, adjust your deployment
workflow so that changes from this repository are copied to the live server.
