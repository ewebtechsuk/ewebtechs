#!/usr/bin/env bash
set -euo pipefail

REMOTE_HOST=${REMOTE_HOST:-82.219.189.219}
REMOTE_PORT=${REMOTE_PORT:-65002}
REMOTE_USER=${REMOTE_USER:-u758780474}
REMOTE_DIR=${REMOTE_DIR:-/home/${REMOTE_USER}/public_html}

if [[ $# -eq 0 ]]; then
  cat <<USAGE
Usage: $0 relative/path [relative/path...]

Compare the MD5 checksum of files under public_html/ with the files on Hostinger.
Override REMOTE_HOST, REMOTE_PORT, REMOTE_USER, or REMOTE_DIR if your credentials change.
USAGE
  exit 1
fi

for rel_path in "$@"; do
  local_path="public_html/${rel_path}"
  remote_path="${REMOTE_DIR}/${rel_path}"

  echo "\n==> ${rel_path}"

  if [[ ! -e "${local_path}" ]]; then
    echo "Local file missing: ${local_path}" >&2
    continue
  fi

  local_hash=$(md5sum "${local_path}" | awk '{print $1}')
  if ! remote_output=$(ssh -p "${REMOTE_PORT}" "${REMOTE_USER}@${REMOTE_HOST}" "md5sum '"${remote_path}"'"); then
    echo "Failed to read remote file ${remote_path}" >&2
    continue
  fi

  remote_hash=$(awk '{print $1}' <<< "${remote_output}")

  echo "local : ${local_hash}"
  echo "remote: ${remote_hash}"

  if [[ "${local_hash}" == "${remote_hash}" ]]; then
    echo "Status: ✔ Files match"
  else
    echo "Status: ✖ Files differ"
  fi

done
