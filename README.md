# eWeb Techs

This repository is prepared to hold the files for the `ewebtechs.com` site.

The `public_html` directory is currently empty. Add your actual site files by
copying them into the `public_html/` directory and committing the results:

1. Copy your website files into the `public_html/` directory.
2. Run `git add public_html` to stage them.
3. Commit the files: `git commit -m "Add site files"`.
4. Push to GitHub: `git push origin main` (use `--force` if overwriting).

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
