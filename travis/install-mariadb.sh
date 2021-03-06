OS_CODENAME=$(lsb_release --codename --short)

apt-get purge mysql*

apt-get install python-software-properties
apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0xcbcb082a1bb943db
add-apt-repository "deb http://ftp.hosteurope.de/mirror/mariadb.org/repo/$VERSION/ubuntu $OS_CODENAME main"

apt-get update

debconf-set-selections <<< "mariadb-server-$VERSION mysql-server/root_password password rootpasswd"
debconf-set-selections <<< "mariadb-server-$VERSION mysql-server/root_password_again password rootpasswd"
DEBIAN_FRONTEND=noninteractive apt-get -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confnew" install mariadb-server

echo "
USE mysql;
UPDATE user SET Password = PASSWORD('') where User = 'root';
FLUSH PRIVILEGES;
" | mysql -u root -prootpasswd

mysql --version
