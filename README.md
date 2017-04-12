![Crunchbutton](www/assets/images/facebook-like.png)

[![Build Status](https://travis-ci.org/crunchbutton/crunchbutton.svg?branch=master)](https://travis-ci.org/crunchbutton/crunchbutton)

For detailed info check out [the wiki](https://github.com/crunchbutton/crunchbutton/wiki).

---

#### Local Setup

1. Install mysql
2. Import the `db/dump.sql`
3. Run the migrate script using `php travis/dbmigrate.php`
4. Create a file called `.env` with `DATABASE_URL` and `DEBUG` set to `1`
5. Run `heroku local local`
6. Open http://localhost:8000

---

#### Standalone Deployment

Deploy cockpit on heroku

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy?template=https://github.com/crunchbutton/crunchbutton&env[THEME]=cockpit2&env[DATABASE_URL]=null&env[USE_ENCRYPTION_KEY]=true&env[ADMIN_LOGIN]=admin&env[ADMIN_PASSWORD]=password&env[ADMIN_NAME]=Super%20Admin)

Deploy crunchbutton on heroku

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy?template=https://github.com/crunchbutton/crunchbutton&env[THEME]=seven&env[DATABASE_URL]=null&env[USE_ENCRYPTION_KEY]=true&env[ADMIN_LOGIN]=admin&env[ADMIN_PASSWORD]=password&env[ADMIN_NAME]=Super%20Admin)

---

#### License
This Community Version is made available under Free Software Foundation's GNU AGPLv3. For the full content of the license, please visit https://www.gnu.org/licenses/agpl-3.0.html.

If the restrictions placed by the license do not meet your needs, Enterprise Version licenses are available from http://ae.studio/.
