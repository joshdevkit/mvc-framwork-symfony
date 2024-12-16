<?php


namespace App\Core\Validations;

trait UseErrors
{
    public function returnForbiddenError()
    {
        http_response_code(403);
        echo '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Invalid Request - Status 500</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    text-align: center;
                    padding: 50px;
                }
                .error-container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ccc;
                    border-radius: 10px;
                    background-color: #f9f9f9;
                }
                h1 {
                    color: #d9534f;
                }
                p {
                    font-size: 16px;
                    color: #555;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1>Forbidden</h1>
                <p>The action fails due to insufficient rights and the response body contains a reason.</p>
                <p>The server was unable to complete your request. Please try again later.</p>
            </div>
        </body>
        </html>';
    }


    public function return404Error()
    {
        http_response_code(404);
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - PAGE NOT FOUND</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background: #f4f4f4;
                    margin: 0;
                    padding: 0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    color: #333;
                }
                .error-container {
                    text-align: center;
                    background: #fff;
                    padding: 30px 40px;
                    border-radius: 10px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
                .error-container h1 {
                    font-size: 80px;
                    margin: 0;
                    color: #e74c3c;
                }
                .error-container h2 {
                    font-size: 24px;
                    margin: 10px 0;
                    color: #333;
                }
                .error-container p {
                    font-size: 16px;
                    margin: 15px 0;
                    color: #555;
                }
                .error-container a {
                    text-decoration: none;
                    color: #3498db;
                    font-weight: bold;
                }
                .error-container a:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1>404</h1>
                <h2>Page not found on the server.</h2>
                <p>The link maybe broken or remove by the service provider.</p>
                <p><a href="/">Return to Home</a></p>
            </div>
        </body>
        </html>
        ';
    }


    public function return405Error($httpMethod, $allowedMethods, $controllName, $uRi)
    {
        echo '
        <!DOCTYPE html>
        <html>
        <head>
            <title>405 Method Not Allowed</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    text-align: center;
                    padding: 50px;
                }
                .error-container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ccc;
                    border-radius: 10px;
                    background-color: #f9f9f9;
                }
                h1 {
                    color: #d9534f;
                }
                p {
                    font-size: 16px;
                    color: #555;
                }
                .details {
                    font-size: 14px;
                    color: #777;
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1>405 Method Not Allowed</h1>
                <p>Target Url: <strong>' . $uRi . '</strong></p>
                <p>The HTTP method <strong>' . htmlspecialchars($httpMethod) . '</strong> is not allowed for the requested resource.</p>
                <div class="details">
                    <p>Allowed Methods: <strong>' . $allowedMethods . '</strong></p>
                    <p><strong>' .  $controllName . '</strong></p>
                </div>
            </div>
        </body>
        </html>';
    }


    public function returnGenericError()
    {
        header("HTTP/1.1 500 Internal Server Error");
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Error</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .error-container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 10px; background-color: #f9f9f9; }
                h1 { color: #d9534f; }
                p { font-size: 16px; color: #555; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1>An Error Occurred</h1>
                <p>Something went wrong. Please try again later.</p>
            </div>
        </body>
        </html>';
    }
}
