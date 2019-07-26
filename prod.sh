git submodule update --init --recursive --remote
npm install
composer install --ignore-platform-reqs
./node_modules/.bin/encore production
bin/console doctrine:schema:update --force
bin/console cache:clear
bin/console doctrine:cache:clear-result
bin/console doctrine:cache:clear-metadata
bin/console doctrine:cache:clear-query
bin/console cache:warmup --env=prod
