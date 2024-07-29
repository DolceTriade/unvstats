
FROM alpine:3.20
RUN apk update && apk upgrade
RUN apk add bash mariadb mariadb-client \
    apache2 \
    apache2-utils \
    curl wget \
    tzdata \
    pwgen \
    php83-apache2 \
    php83-cli \
    php83-phar \
    php83-zlib \
    php83-zip \
    php83-bz2 \
    php83-ctype \
    php83-curl \
    php83-pdo_mysql \
    php83-mysqli \
    php83-json \
    php83-pecl-mcrypt \
    php83-xml \
    php83-dom \
    php83-iconv \
    php83-xdebug \
    php83-session \
    php83-intl \
    php83-gd \
    php83-mbstring \
    php83-apcu \
    php83-opcache \
    php83-tokenizer \
    php83-simplexml \
    python3 py3-mysqlclient py3-pillow py3-scipy

RUN cp /usr/share/zoneinfo/UTC /etc/localtime && \
    echo "UTC" > /etc/timezone && \
    mkdir -p /var/lib/mysql && \
    mkdir -p /run/mysqld && chown -R mysql:mysql /run/mysqld /var/lib/mysql && \
    mkdir -p /run/apache2 && chown -R apache:apache /run/apache2 && chown -R apache:apache /var/www/localhost/htdocs/ && \
    sed -i 's#\#LoadModule rewrite_module modules\/mod_rewrite.so#LoadModule rewrite_module modules\/mod_rewrite.so#' /etc/apache2/httpd.conf && \
    sed -i 's#ServerName www.example.com:80#\nServerName localhost:80#' /etc/apache2/httpd.conf && \
    sed -i 's/skip-networking/\#skip-networking/i' /etc/my.cnf.d/mariadb-server.cnf && \
    sed -i '/mariadb\]/a log_error = \/var\/lib\/mysql\/error.log' /etc/my.cnf.d/mariadb-server.cnf && \
    sed -i -e"s/^bind-address\s*=\s*127.0.0.1/bind-address = 0.0.0.0/" /etc/my.cnf.d/mariadb-server.cnf && \
    sed -i '/mariadb\]/a skip-external-locking' /etc/my.cnf.d/mariadb-server.cnf && \
    sed -i '/mariadb\]/a general_log = ON' /etc/my.cnf.d/mariadb-server.cnf && \
    sed -i '/mariadb\]/a general_log_file = \/var\/lib\/mysql\/query.log' /etc/my.cnf.d/mariadb-server.cnf

RUN sed -i 's#display_errors = Off#display_errors = On#' /etc/php83/php.ini && \
    sed -i 's#upload_max_filesize = 2M#upload_max_filesize = 100M#' /etc/php83/php.ini && \
    sed -i 's#post_max_size = 8M#post_max_size = 100M#' /etc/php83/php.ini && \
    sed -i 's#session.cookie_httponly =#session.cookie_httponly = true#' /etc/php83/php.ini && \
    sed -i 's#error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT#error_reporting = E_ALL#' /etc/php83/php.ini


# Configure xdebug
RUN echo "zend_extension=xdebug.so" > /etc/php83/conf.d/xdebug.ini && \
    echo -e "\n[XDEBUG]"  >> /etc/php83/conf.d/xdebug.ini && \
    echo "xdebug.mode="debug"" >> /etc/php83/conf.d/xdebug.ini && \
    echo "xdebug.discover_client_host=false" >> /etc/php83/conf.d/xdebug.ini && \
    echo "xdebug.idekey=PHPSTORM" >> /etc/php83/conf.d/xdebug.ini && \
    echo "xdebug.log=\"/tmp/xdebug.log\"" >> /etc/php83/conf.d/xdebug.ini

COPY entry.sh /entry.sh
COPY web/* /var/www/localhost/htdocs/

RUN chmod u+x /entry.sh

WORKDIR /var/www/localhost/htdocs/

EXPOSE 80
EXPOSE 3306

ENTRYPOINT ["/entry.sh"]
