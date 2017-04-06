![Logo](http://crunchbutton.com/assets/images/facebook-like.png)

[![BuildStatus](https://travis-ci.com/youbeo/crunchbutton.svg?token=hxz6fVTQWxPXmgzxg8Yb&branch=master)](https://travis-ci.com/youbeo/crunchbutton)

For detailed info check out [the wiki](https://github.com/youbeo/crunchbutton/wiki).

---

#### Local Setup

1. Create a file called `.env` with `DATABASE_URL` and `DEBUG` set
2. Run `heroku local local`
3. Open http://localhost:8000

---

#### Standalone Deployment

Deploy cockpit on heroku

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy?template=https://github.com/youbeo/crunchbutton&env[THEME]=cockpit2&env[DATABASE_URL]=null&env[USE_ENCRYPTION_KEY]=true&env[ADMIN_LOGIN]=admin&env[ADMIN_PASSWORD]=password&env[ADMIN_NAME]=Super%20Admin)

Deploy crunchbutton on heroku

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy?template=https://github.com/youbeo/crunchbutton&env[THEME]=seven&env[DATABASE_URL]=null&env[USE_ENCRYPTION_KEY]=true&env[ADMIN_LOGIN]=admin&env[ADMIN_PASSWORD]=password&env[ADMIN_NAME]=Super%20Admin)