<?php

namespace App\Service;

class DomainService
{
    public static function getFileContent(string $domain, string $dir): string
    {
        return <<<LINE
server {
    server_name $domain www.$domain;
    root /var/www/$dir/public;

    if (\$host = $domain) {
        return 301 https://www.\$host\$request_uri;
    }

    location / {
        try_files \$uri /index.php\$is_args\$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;

        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT \$realpath_root;

        internal;
    }

    location ~ \.php$ {
        return 404;
    }

    error_log /var/log/nginx/$dir.error.log;
    access_log /var/log/nginx/$dir.access.log;
}
LINE;
    }
}