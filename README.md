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

## GitHub secrets

This repository now includes a GitHub Actions workflow that deploys the
contents of `public_html/` to Hostinger whenever changes are pushed to the
`main` branch. Before that automation can succeed, add the following secrets in
your GitHub repository settings (Settings → Secrets and variables → Actions):

| Secret name | Description |
|-------------|-------------|
| `HOSTINGER_FTP_HOST` | Hostname for your Hostinger FTP/FTPS server. |
| `HOSTINGER_FTP_PORT` | Port number (defaults to `21` if not set; use `65002` for SFTP on Hostinger). |
| `HOSTINGER_FTP_USERNAME` | FTP username. |
| `HOSTINGER_FTP_PASSWORD` | FTP password. |
| `HOSTINGER_FTP_SERVER_DIR` | Remote directory to upload into (defaults to `/public_html/`). |
| `HOSTINGER_FTP_PROTOCOL` | Optional. Override the protocol (`ftps` by default). Set to `sftp` if your Hostinger plan only allows SFTP. |

> **Tip:** Hostinger typically recommends FTPS (explicit TLS) on port 21 for
> GitHub Actions. If you must use SFTP on port 65002, set the
> `HOSTINGER_FTP_PROTOCOL` secret to `sftp` and the `HOSTINGER_FTP_PORT`
> secret to `65002`.

Once the secrets are configured, every push that touches files under
`public_html/` will trigger the deployment workflow. If any of the required
secrets are missing, the workflow now fails immediately with an explanatory
error so it is clear that no deployment occurred. When the secrets are present,
the workflow validates that `public_html/` exists before deploying, and it
defaults to FTPS on port 21 if no protocol or port secret is provided. You can
monitor the run on GitHub under the **Actions** tab.

> **Important:** Keep the `HOSTINGER_FTP_PASSWORD` secret up to date whenever
> the credential changes. Update it immediately if Hostinger issues a new
> password so automated deployments continue to work.

### Update the FTP password secret

1. Open your repository on GitHub and navigate to **Settings → Secrets and
   variables → Actions**.
2. Locate `HOSTINGER_FTP_PASSWORD` and choose **Update secret**.
3. Paste the latest password provided by Hostinger and click **Save changes**.
4. Re-run the latest failed workflow from the **Actions** tab to confirm the
   deployment succeeds with the refreshed credential.

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
