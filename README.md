# ex19

Exercise 19-1: PHP and MySQL product database application using PDO.

## Run with XAMPP

1. Start Apache and MySQL in the XAMPP Control Panel.
2. Import `database.sql` using phpMyAdmin if the sample database is not set up.
3. Check the MySQL credentials in `database.php`.
4. Open `http://localhost:8080/ex19/` (this XAMPP installation uses port 8080).

The page lists guitar products, deletes `Fender Telecaster` when it exists, and
inserts a sample product if its product code is not already in use.
