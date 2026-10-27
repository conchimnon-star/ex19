<?php

$category_name = 'Guitars';
$messages = [];
$initial_products = [];
$products = [];
$database_error = '';
$form_errors = [];

try {
    require_once __DIR__ . '/database.php';
    require_once __DIR__ . '/product_db.php';

    // Chụp danh sách ban đầu trước khi thực hiện các thao tác của bài tập.
    $initial_products = get_products_by_category_name($category_name);

    try {
        $fender = get_product_by_name('Fender Telecaster');
        if ($fender === null) {
            $messages[] = ['info', 'Không tìm thấy Fender Telecaster; không có sản phẩm nào bị xóa.'];
        } elseif (!isset($fender['productCode']) || trim($fender['productCode']) === '') {
            $messages[] = ['error', 'Không thể xóa Fender Telecaster vì mã sản phẩm không hợp lệ.'];
        } elseif (delete_product($fender['productCode'])) {
            $messages[] = ['success', 'Đã xóa sản phẩm Fender Telecaster khỏi cơ sở dữ liệu.'];
        } else {
            $messages[] = ['error', 'Không xóa được Fender Telecaster.'];
        }
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        $messages[] = ['error', 'Đã xảy ra lỗi khi tìm hoặc xóa Fender Telecaster.'];
    }

    $category_id = get_category_id_by_name($category_name);
    if ($category_id === null) {
        $messages[] = ['error', 'Không tìm thấy danh mục Guitars; hãy nhập database.sql trước.'];
    } else {
        try {
            add_product([
                'categoryID' => $category_id,
                'productCode' => 'ex19sample',
                'productName' => 'Exercise 19-1 Sample Guitar',
                'description' => 'Sản phẩm mẫu được thêm bởi bài thực hành Exercise 19-1.',
                'listPrice' => '499.99',
            ]);
            $messages[] = ['success', 'Đã thêm sản phẩm mẫu vào cơ sở dữ liệu.'];
        } catch (PDOException $exception) {
            if (isset($exception->errorInfo[1]) && (int) $exception->errorInfo[1] === 1062) {
                $messages[] = ['info', 'Mã sản phẩm ex19sample đã tồn tại; không thêm bản ghi trùng.'];
            } else {
                error_log($exception->getMessage());
                $messages[] = ['error', 'Không thể thêm sản phẩm mẫu.'];
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_product') {
            $product_code = trim(posted_string('product_code'));
            $product_name = trim(posted_string('product_name'));
            $description = trim(posted_string('description'));
            $price_input = trim(posted_string('list_price'));
            $price = filter_var($price_input, FILTER_VALIDATE_FLOAT);

            if ($product_code === '' || strlen($product_code) > 10) {
                $form_errors[] = 'Mã sản phẩm phải có từ 1 đến 10 ký tự.';
            }
            if ($product_name === '' || strlen($product_name) > 255) {
                $form_errors[] = 'Tên sản phẩm là bắt buộc và không được quá 255 ký tự.';
            }
            if ($description === '') {
                $form_errors[] = 'Vui lòng nhập mô tả sản phẩm.';
            }
            if ($price === false || $price <= 0 || $price > 99999999.99) {
                $form_errors[] = 'Giá phải là số lớn hơn 0 và không vượt quá 99.999.999,99.';
            }

            if ($form_errors === []) {
                try {
                    add_product([
                        'categoryID' => $category_id,
                        'productCode' => $product_code,
                        'productName' => $product_name,
                        'description' => $description,
                        'listPrice' => number_format((float) $price, 2, '.', ''),
                    ]);
                    $messages[] = ['success', 'Đã thêm sản phẩm "' . $product_name . '" vào cơ sở dữ liệu.'];
                } catch (PDOException $exception) {
                    if (isset($exception->errorInfo[1]) && (int) $exception->errorInfo[1] === 1062) {
                        $form_errors[] = 'Mã sản phẩm đã tồn tại. Hãy chọn một mã khác.';
                    } else {
                        error_log($exception->getMessage());
                        $form_errors[] = 'Không thể thêm sản phẩm do lỗi cơ sở dữ liệu.';
                    }
                }
            }
        }
    }

    $products = get_products_by_category_name($category_name);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    $database_error = 'Không thể kết nối hoặc truy vấn cơ sở dữ liệu. Hãy kiểm tra MySQL và cấu hình trong database.php.';
}

function escape_html($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function posted_string(string $key): string
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? $value : '';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Exercise 19-1 - Quản lý sản phẩm</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container">
    <header class="page-header">
        <p class="eyebrow">Professional PHP for Working with MySQL</p>
        <h1>Exercise 19-1: Làm việc với cơ sở dữ liệu</h1>
        <p>Danh sách sản phẩm thuộc danh mục <strong><?php echo escape_html($category_name); ?></strong></p>
    </header>

    <?php if ($database_error !== '') : ?>
        <div class="notice error" role="alert"><?php echo escape_html($database_error); ?></div>
    <?php else : ?>
        <section class="panel">
            <h2>Kết quả thao tác</h2>
            <?php foreach ($messages as [$type, $message]) : ?>
                <p class="notice <?php echo escape_html($type); ?>" role="status"><?php echo escape_html($message); ?></p>
            <?php endforeach; ?>
        </section>

        <section class="panel">
            <h2>Sản phẩm trước thao tác</h2>
            <?php if ($initial_products === []) : ?>
                <p>Chưa có sản phẩm nào trong danh mục Guitars.</p>
            <?php else : ?>
                <?php render_product_table($initial_products); ?>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h2>Thêm sản phẩm vào danh mục Guitars</h2>
            <?php foreach ($form_errors as $form_error) : ?>
                <p class="notice error" role="alert"><?php echo escape_html($form_error); ?></p>
            <?php endforeach; ?>
            <form method="post" action="index.php" class="product-form">
                <input type="hidden" name="action" value="add_product">
                <label for="product_code">Mã sản phẩm <span>(tối đa 10 ký tự)</span></label>
                <input id="product_code" name="product_code" type="text" maxlength="10" required
                       value="<?php echo escape_html(posted_string('product_code')); ?>">

                <label for="product_name">Tên sản phẩm</label>
                <input id="product_name" name="product_name" type="text" maxlength="255" required
                       value="<?php echo escape_html(posted_string('product_name')); ?>">

                <label for="description">Mô tả</label>
                <textarea id="description" name="description" rows="3" required><?php
                    echo escape_html(posted_string('description'));
                ?></textarea>

                <label for="list_price">Giá (USD)</label>
                <input id="list_price" name="list_price" type="number" min="0.01" step="0.01" required
                       value="<?php echo escape_html(posted_string('list_price')); ?>">

                <button type="submit">Thêm sản phẩm</button>
            </form>
        </section>

        <section class="panel">
            <h2>Sản phẩm sau thao tác</h2>
            <?php if ($products === []) : ?>
                <p>Chưa có sản phẩm nào trong danh mục Guitars.</p>
            <?php else : ?>
                <?php render_product_table($products); ?>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
<?php

function render_product_table(array $products): void
{
    ?>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th scope="col">Mã sản phẩm</th>
                <th scope="col">Tên sản phẩm</th>
                <th scope="col">Danh mục</th>
                <th scope="col">Giá</th>
                <th scope="col">Mô tả</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $product) : ?>
                <tr>
                    <td><?php echo escape_html($product['productCode']); ?></td>
                    <td><?php echo escape_html($product['productName']); ?></td>
                    <td><?php echo escape_html($product['categoryName']); ?></td>
                    <td class="price">$<?php echo escape_html(number_format((float) $product['listPrice'], 2)); ?></td>
                    <td><?php echo escape_html($product['description']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
