if git pull | grep -q 'Already up to date.'; then
  exit 1;
fi

php bin/console doctrine:schema:update --force
yarn build
php bin/console cache:clear