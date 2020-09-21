FROM ubuntu:20.04

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=Europe/Moscow
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update
RUN apt-get install -y apt-utils

RUN apt-get install -y apache2
RUN apt-get install -y mysql-server
RUN apt-get install -y libapache2-mod-php php-mysqli
RUN apt-get install -y php-xdebug
RUN apt-get install -y composer
RUN apt-get install -y php-intl php-imagick php-xml php-memcache php-curl

RUN mkdir -m 777 /var/run/mysqld
RUN sed -i 's/bind-address/#bind-address/' /etc/mysql/mysql.conf.d/mysqld.cnf
RUN echo 'default-time-zone="+03:00"' >> /etc/mysql/mysql.conf.d/mysqld.cnf

RUN /etc/init.d/mysql start && \
    mysql -e "CREATE USER 'project'@'%' IDENTIFIED BY 'project'" && \
    mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'project'@'%' WITH GRANT OPTION" && \
    mysql -e "FLUSH PRIVILEGES" && \
    mysql -e "CREATE DATABASE project" && \
    /etc/init.d/mysql stop

RUN echo "xdebug.remote_enable=true" >> /etc/php/7.4/mods-available/xdebug.ini && \
    echo "xdebug.remote_host=docker-host" >> /etc/php/7.4/mods-available/xdebug.ini && \
    echo "xdebug.remote_autostart=true" >> /etc/php/7.4/mods-available/xdebug.ini

RUN sed -i 's|/var/www/html|/app/web|' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's|/var/www|/app|' /etc/apache2/apache2.conf

EXPOSE 80 3306
CMD /app/update-docker-host-ip && \
    /etc/init.d/apache2 start && \
    /etc/init.d/mysql start && \
    bash

