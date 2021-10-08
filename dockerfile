FROM php:7.4-apache
ENV APP_RUN_USER=tpaloc \
    APP_RUN_UID=1000 \
    APP_RUN_GROUP=tpaloc \
    APP_RUN_GID=1000 \
    APP_HOME=/home/tpaloc \
    APACHE_RUN_USER=tpaloc

RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    wget \
    mc \
    pgp \
    iputils-ping \
    zip \
    libzip-dev \
    curl \
    git-core \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip mysqli pdo pdo_mysql \
    && docker-php-ext-enable pdo_mysql \
    && curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer \
    && wget https://get.symfony.com/cli/installer -O - | bash \
    && mv /root/.symfony/bin/symfony /usr/local/bin/symfony \
    && git config --global user.name "Tomasz Paloc" \
    && git config --global user.email "tomasz.paloc@gmail.com"

# Install gosu to easly change UID
# RUN gpg --keyserver pool.sks-keyservers.net --recv-keys B42F6819007F00F88E364FD4036A9C25BF357DD4
# RUN curl -sSLo \
#         /usr/local/bin/gosu \
#         "https://github.com/tianon/gosu/releases/download/1.2/gosu-$(dpkg --print-architecture)" \
#  && curl -sSLo \
#         /usr/local/bin/gosu.asc \
#         "https://github.com/tianon/gosu/releases/download/1.2/gosu-$(dpkg --print-architecture).asc" \
#  && gpg --verify /usr/local/bin/gosu.asc \
#  && rm /usr/local/bin/gosu.asc \
#  && chmod u+s,+x /usr/local/bin/gosu

# Configure user and group
RUN groupadd -g "${APP_RUN_GID}" "${APP_RUN_GROUP}" \
 && useradd -d "${APP_HOME}" -u "${APP_RUN_UID}" -g "${APP_RUN_GID}" -m -s /bin/bash "${APP_RUN_USER}" \
 && echo "${APP_RUN_USER}:${APP_RUN_USER}" | chpasswd

RUN adduser "${APP_RUN_USER}" sudo

# Configure sudo command
# ADD sudoers.d/ /etc/sudoers.d/
# RUN adduser "${APP_RUN_USER}" sudo \
# && chmod -R 0440 /etc/sudoers.d/*

# USER "${APP_RUN_USER}"
