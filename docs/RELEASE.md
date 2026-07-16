# Maintainer: tagging and GitHub Release

## Prerequisites

- [`CHANGELOG.md`](CHANGELOG.md) updated with the new version and date (`[Unreleased]` moved to a numbered section).
- [`UPGRADING.md`](UPGRADING.md) updated if there are migration notes.
- CI green on `main` ([workflow](../.github/workflows/ci.yml)).
- [Release security checklist (12.4.1)](SECURITY.md#release-security-checklist-1241) reviewed.

## Version bump

1. Decide the next version (`MAJOR.MINOR.PATCH`, semver).
2. Edit [`CHANGELOG.md`](CHANGELOG.md): move `[Unreleased]` content into `[x.y.z] - YYYY-MM-DD`, add empty `[Unreleased]` at the top.
3. Commit on `main`, e.g. `docs: prepare release x.y.z`.

## Tag and push

Replace `x.y.z` with the real version (Composer uses `x.y.z`; the Git tag uses a `v` prefix).

```bash
git checkout main
git pull origin main
git tag -a vx.y.z -m "Release x.y.z"
git push origin main
git push origin vx.y.z
```

## GitHub Release

[`release.yml`](../.github/workflows/release.yml) creates a release from the annotated tag message and `CHANGELOG.md` when you push `v*` tags.

You can still edit the release notes manually on GitHub if needed.

## Packagist

If the package is registered on [Packagist](https://packagist.org/), a new tag is picked up automatically after the push.

## Automated sync

[`sync-releases.yml`](../.github/workflows/sync-releases.yml) can create or backfill releases from existing tags (scheduled or manual).

After creating the release commit and tag, run `make check-no-cursor-coauthor` again **before** `git push` (REQ-GIT-001). The release commit itself is not covered by an earlier `release-check` run.
