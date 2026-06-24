# LottieFiles

## Packages Installation

- First of all create your personal access token on github.com
  <a href="https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/creating-a-personal-access-token">by
  following this link.</a>
- Copy `//npm.pkg.github.com/:_authToken=<YOUR-TOKEN>` and Paste this line to your `.npmrc` file.
- Replace `YOUR-TOKEN` with access token you just generated on github.com

### 👉 Run `pnpm install`

- To install all the dependencies.

## Running Locally in Development mode

- First of all Install WordPress on your machine locally.
  <a href="https://skillcrush.com/blog/install-wordpress-mac/">by following this link.</a>
- Download `plugin-wordpress` Repo directly to the `plugin` of your locally installed WordPress.

### 👉 Run `pnpm start`

- Command to run the block and watch for any changes.
- Once changes are saved. they will be reflected in the block.

### 👉 Run `pnpm build`

- Runnning this command will create build of `plugin-wordpress` plugin.

## Running on Docker in Development mode

- First of all install Docker on your machine. <a href="https://docs.docker.com/desktop/mac/install/">by following this
  link.</a>

### 👉 Run `pnpm start:wordpress` to start WordPress Docker environment.

- Running this command will Start Docker environment and Download Fresh version of WordPress on Docker.
- It will automatically install and run `plugin-wordpress` plugin.
- By default port 8888, meaning that the local environment will be available at
- Go to [localhost:8888/wp-admin](localhost:8888/wp-admin) to login to the admin dashboard with the default credentials
  username: `admin` and Password: `password`.

### Configure Port WordPress Docker environment.

- You can configure the port of your choice. so it doesn’t clash with another server by replacing command in
  Package.json. from this `"start:wordpress": "wp-env start",` to this
  `"start:wordpress": "WP_ENV_PORT=YOUR_PORT wp-env start",`.

### 👉 Run `pnpm stop:wordpress`

- Use to stop wordpress docker environment.

## Running in Published mode

- First of all create build by running `pnpm build` command.

### 👉 Run `pnpm plugin-zip`

- It will create plugin zip.
- You can upload this zip to WordPress.com/Wordpress.com or you can directly install it to your WordPress installation using Plugins
  menu `Add new`.
