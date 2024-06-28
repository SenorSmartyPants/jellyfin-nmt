FROM php:7.3.19-apache

# environment settings
ARG DEBIAN_FRONTEND="noninteractive"
ENV LANG="C.UTF-8"


#mkdir man fixes jre install issue on php:apache image
RUN \
 echo "**** install runtime packages ****" && \
 mkdir -p /usr/share/man/man1 && \ 
 apt-get update && \
 apt-get install -y \
	--no-install-recommends \
        libfreetype6-dev \
	libjpeg62-turbo-dev \
	libpng-dev \
	mediainfo && \
 docker-php-ext-configure gd --with-freetype-dir=/usr/include/freetype2 --with-png-dir=/usr/include --with-jpeg-dir=/usr/include && \
 docker-php-ext-install -j$(nproc) gd && \
 echo "**** remove accessibility properties that sometimes cause an error ****" && \
 find /etc -name "accessibility.properties" -exec rm -fv '{}' + && \
 find /usr -name "accessibility.properties" -exec rm -fv '{}' + && \	
 echo "**** cleanup ****" && \
 rm -rf \
	/tmp/* \
	/var/lib/apt/lists/* \
	/var/tmp/*
