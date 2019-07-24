git submodule update --init --recursive --remote
npm install
composer install --ignore-platform-reqs
cd public/assets
bower install --allow-root
cd ../..
./node_modules/.bin/encore production
bin/console doctrine:schema:update --force
bin/console cache:clear
bin/console cache:warmup --env=prod
