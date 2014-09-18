git subsplit init git@github.com:laravel/framework.git
git subsplit publish --heads="0.9.2-dev" --no-tags src/Brainwave/Config:git@github.com:n-brainwave/config.git
rm -rf .subsplit/
