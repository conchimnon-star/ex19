<?php

function get_products_by_category_name(string $category_name): array
{
    global $db;

    $statement = $db->prepare(
        "SELECT p.productCode, p.productName, p.description, p.listPrice,
                c.categoryName
         FROM products AS p
         INNER JOIN categories AS c ON c.categoryID = p.categoryID
         WHERE c.categoryName = :categoryName
            OR (:includeGuitarCategories = 1 AND c.categoryName LIKE '%Guitar%')
         ORDER BY p.productName"
    );
    $statement->execute([
        ':categoryName' => $category_name,
        ':includeGuitarCategories' => $category_name === 'Guitars' ? 1 : 0,
    ]);

    return $statement->fetchAll();
}

function get_category_id_by_name(string $category_name): ?int
{
    global $db;

    $statement = $db->prepare(
        "SELECT categoryID
         FROM categories
         WHERE categoryName = :categoryName
            OR (:includeGuitarCategories = 1 AND categoryName LIKE '%Guitar%')
         ORDER BY (categoryName = 'Electric Guitars') DESC, categoryID
         LIMIT 1"
    );
    $statement->execute([
        ':categoryName' => $category_name,
        ':includeGuitarCategories' => $category_name === 'Guitars' ? 1 : 0,
    ]);
    $category_id = $statement->fetchColumn();

    return $category_id === false ? null : (int) $category_id;
}

function get_product_by_name(string $product_name): ?array
{
    global $db;

    $statement = $db->prepare(
        'SELECT p.productCode, p.productName, p.description, p.listPrice,
                p.categoryID, c.categoryName
         FROM products AS p
         INNER JOIN categories AS c ON c.categoryID = p.categoryID
         WHERE p.productName = :productName
         ORDER BY p.productID
         LIMIT 1'
    );
    $statement->execute([':productName' => $product_name]);
    $product = $statement->fetch();

    return $product ?: null;
}

function add_product(array $product): void
{
    global $db;

    $statement = $db->prepare(
        'INSERT INTO products (categoryID, productCode, productName, description, listPrice)
         VALUES (:categoryID, :productCode, :productName, :description, :listPrice)'
    );
    $statement->execute([
        ':categoryID' => $product['categoryID'],
        ':productCode' => $product['productCode'],
        ':productName' => $product['productName'],
        ':description' => $product['description'],
        ':listPrice' => $product['listPrice'],
    ]);
}

function delete_product(string $product_code): bool
{
    global $db;

    $statement = $db->prepare('DELETE FROM products WHERE productCode = :productCode');
    $statement->execute([':productCode' => $product_code]);

    return $statement->rowCount() > 0;
}
