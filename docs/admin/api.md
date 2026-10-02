# Import / export API

Token-protected JSON webservices for syncing members and activities with an
external system (a federation database, a training website). Successor of the
legacy eBrigade `api/` scripts: same payloads, same `errnum` codes.

Code: `routes/api.php`, `App\Http\Controllers\Api\V1\ImportExportController`,
`App\Services\Api\*`, `App\Http\Middleware\RequireApiToken`. Tests:
`tests/Feature/ApiV1Test.php`.

## Endpoints

| Method + path                   | Legacy alias                  | Token  | Does                                   |
| ------------------------------- | ----------------------------- | ------ | -------------------------------------- |
| `POST /api/v1/personnel/search` | `POST /api/export/search.php` | export | Search members, with valid competences |
| `POST /api/v1/personnel/import` | `POST /api/import/people.php` | import | Create or update a member              |
| `POST /api/v1/events/import`    | `POST /api/import/event.php`  | import | Create or update an activity           |

The legacy aliases exist so clients written for eBrigade keep working after the
upgrade. New clients use `/api/v1`. All routes: 60 calls/minute per IP.

## Activation and tokens

Set in **Administration ▸ Configuration** (legacy `configuration` rows):

| Setting            | Row | Effect                                                   |
| ------------------ | --- | -------------------------------------------------------- |
| `webservice_key`   | 50  | Export token. Empty = export API off (`errnum` 10, 503). |
| `import_api`       | 64  | Import switch. Off = import API off (`errnum` 10, 503).  |
| `import_api_token` | 66  | Import token. Empty = import API off.                    |

Send the token as `Authorization: Bearer <token>` (preferred) or as a `token`
field in the JSON body (legacy). Use a long random value, e.g.
`openssl rand -hex 32`. A wrong token is logged in the `security` canal.

## Requests and responses

- Body: raw JSON (read whatever the `Content-Type`, as eBrigade did). Field names are
  the legacy ones.
- Search answers a JSON list. Imports answer
  `{"status":"success","errnum":0,"message":"…","id":<P_ID or E_CODE>}`
  with **201** on create, **200** on update.
- Every refusal answers `{"status":"error","errnum":<code>,"message":"…"}`.

| `errnum`             | HTTP | Meaning                                           |
| -------------------- | ---- | ------------------------------------------------- |
| 10                   | 503  | API not activated                                 |
| 20                   | 400  | Body is not a non-empty JSON object               |
| 30                   | 401  | Token missing                                     |
| 40                   | 403  | Wrong token                                       |
| 50                   | 422  | Search without any criterion (new in OpenBrigade) |
| 1050-1320            | 422  | Invalid or missing field, same codes as eBrigade  |
| 1400-1430 (events)   | 422  | Session dates invalid or out of order             |
| 1450                 | 422  | Unknown activity type                             |
| 1400 (members), 1460 | 409  | Duplicate member / activity                       |
| 1432                 | 409  | Update: `P_ID` does not match `P_NOM`             |
| 1431, 1470           | 404  | Update target not found                           |

The exact code for each field is in the service that checks it
(`PersonnelImportService`, `EventImportService`).

### `personnel/search`

Criteria (ANDed, case-insensitive prefix match, exact with `"qstrict":1`):
`id`, `username`, `lastname`, `firstname`, `email`, `phone` (matches `P_PHONE`
or `P_PHONE2`). `"exclude_old":1` drops former members. External people
(`P_STATUT = EXT`) are never returned. Each result: `id`, `username`,
`lastname`, `firstname`, `email`, `birthdate`, `phone`, `phone2`, `sexe`,
`section`, `skills` (`{PS_ID: TYPE}` of non-expired competences).

### `personnel/import`

`action`: `ImportPersonnel` (create) or `UpdatePersonnel` (update `P_ID`; its
`P_NOM` must match). Required: `P_CODE`, `P_STATUT`, `P_NOM`, `P_PRENOM`,
`P_BIRTHDATE`, `P_SEXE`, `P_CIVILITE`, `P_DATE_ENGAGEMENT`, `P_SECTION`.
Optional: the other `pompier` identity/contact fields of the legacy example,
`P_GRADE` (default `-`), `P_PAYS` (default 65), `P_MDP` (md5 hash; omitted =
no password on create, unchanged on update), and `competences`
(`[{"id":2,"expiration":"2030-12-31"}]`), which **replaces** all the member's
competences when non-empty. Dates are `Y-m-d`. New members are created hidden
(`P_HIDE = 1`); external members (`EXT`) get no access group.

### `events/import`

`event_code` `0` creates, any other value rewrites that activity. Required:
`event_name`, `event_type` (`TE_CODE`), `location`, `address`, `section`,
`event_sessions` (`[{"session_id":1,"start":"2026-11-17 10:15","end":"…"}]`,
ids 1..n, chronological, `Y-m-d H:i`). Optional: `competence` (a training
competence), `type_formation` (`I`/`R`), `stagiaires`, `contact_entreprise`,
`contact_tel`, `telephone`, `comment`, `tarif`, `url`, and `people`
(`[{"user_id":560,"function_id":5}]`), each registered on every session.

## Legacy inventory

The eBrigade `api/` directory was removed from `archive/legacy_app/` once
ported (git history keeps it). What it held:

| Legacy file                                                  | Consumer                                               | Status                 |
| ------------------------------------------------------------ | ------------------------------------------------------ | ---------------------- |
| `api/export/search.php`                                      | External systems (export token)                        | `personnel/search`     |
| `api/import/people.php`                                      | External systems (import token)                        | `personnel/import`     |
| `api/import/event.php`                                       | External systems (import token)                        | `events/import`        |
| `api/export/test/search_people.php`, `api/import/test/*.php` | Admin demo pages (perm 14) calling the endpoints above | Not ported: use `curl` |
| `api/index.php`, `*/index.php`                               | Empty directory guards                                 | Not needed             |

No legacy screen calls these endpoints; only external clients do. Two other
uses of `webservice_key` stay legacy-only: per-section SOAP keys
(`section.WEBSERVICE_KEY`, set by `save_section.php`) and the "Accès
Webservice" SOAP-call reports in `export-sql-liste.php`. The SOAP server itself
is not part of the eBrigade sources shipped here. `import_api.php` /
`fonctions_import.php` are an outbound client (pull from `import_api_url`),
tracked separately in [legacy-mapping.md](../dev/legacy-mapping.md).

### Parity notes

Same request fields, same `errnum` codes and messages, same search output.
Differences a client may notice:

- `errnum` is a JSON number (`30`); eBrigade sent a string (`"30"`).
- Refusals carry a real HTTP status; eBrigade always answered 200.
- Import successes add the created or updated `id`.
- Search with no criterion returns `errnum` 50 instead of an SQL error.
- `UpdatePersonnel` works on any member whose `P_NOM` matches; eBrigade also
  required `ID_API = P_ID`, which its own `ImportPersonnel` never set, so
  members created through the API could not be updated.
- A new member without `P_MDP` has no password (as with the native create form)
  instead of a random one.

## Example

```bash
curl -X POST https://openbrigade.example.org/api/v1/personnel/search \
  -H "Authorization: Bearer $OB_EXPORT_TOKEN" \
  -H 'Content-Type: application/json' \
  -d '{"lastname":"dupont","exclude_old":1}'
```
