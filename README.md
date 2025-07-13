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
