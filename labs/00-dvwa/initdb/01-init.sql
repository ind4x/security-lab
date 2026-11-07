-- Grant global privileges to dvwa user so DVWA's setup script can DROP/CREATE database
GRANT ALL PRIVILEGES ON *.* TO 'dvwa'@'%';
FLUSH PRIVILEGES;
