# eWeb Techs

This repository is prepared to hold the files for the `ewebtechs.com` site.

The `public_html` directory is currently empty. If you already have a working
WordPress installation at `/htdocs/eWeb\ Techs/public_html` on your local
machine, copy those files into this repository's `public_html/` directory and
commit the results:

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

This README was added from Codex environment which cannot connect to the
original server or GitHub, so you must perform the above steps on a machine
with network access.
