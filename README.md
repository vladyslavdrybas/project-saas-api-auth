# SAAS auth API

## About
Small service to check customer's subscription. Can be used to restrict access to other services.

TODO: cache instead of database.

## DISCLAIMER

<span style="color:#EEDD82; font-weight: bold; font-size: 16px;">This project is a personal pet project created solely for educational and study purposes.</span>

The code provided in this repository is not intended for production or commercial use.
Users are advised that the code may contain incomplete implementations, unoptimized features,
or potential security vulnerabilities that have not been addressed.


<span style="color:#EEDD82; font-weight: bold; font-size: 16px;">Important Note:</span> Any use of this code for production, commercial, or any critical purpose is
strictly discouraged. The author accepts no responsibility for any issues or damages arising
from the use of this code outside of its intended study and learning scope.

By accessing or using any part of this project, you agree to use it solely for non-commercial,
educational purposes and acknowledge that it is provided "as is" without any warranties or
guarantees of functionality or safety.

<span style="color:#EEDD82; font-weight: bold; font-size: 16px;">
Thank you for understanding!
</span>

## Deployment
you can use default php, nginx, postgresql images. best to use with traefik.
* create `.env` and `.env.local`
* run `env-build` to fill `.env` file with local variables
* run `app-run-local` to start project on your local machine
* open php container and install dependencies.
