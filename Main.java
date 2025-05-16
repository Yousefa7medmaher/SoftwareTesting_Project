package org.example;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import java.time.Duration;
import java.util.UUID;

public class Main {
    public static void main(String[] args) {
        System.setProperty("webdriver.chrome.driver", "C:\\Users\\A\\Downloads\\chromedriver-win64\\chromedriver-win64\\chromedriver.exe");

        WebDriver driver = new ChromeDriver();
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(10));

        String randomName = "User" + UUID.randomUUID().toString().substring(0, 5);
        String randomEmail = "user" + UUID.randomUUID().toString().substring(0, 5) + "@gmail.com";
        String password = "pass123456";

        try {
            driver.get("http://localhost/Ecommerce/FrontEnd/register.html");
            wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("name"))).sendKeys(randomName);
            driver.findElement(By.id("email")).sendKeys(randomEmail);
            driver.findElement(By.id("password")).sendKeys(password);
            driver.findElement(By.id("confirmPassword")).sendKeys(password);
            driver.findElement(By.cssSelector("button.register-btn")).click();
            wait.until(ExpectedConditions.urlContains("login"));
            System.out.println("✅ Registration successful");

            driver.get("http://localhost/Ecommerce/FrontEnd/login.html");
            wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("email"))).sendKeys(randomEmail);
            driver.findElement(By.id("password")).sendKeys(password);
            driver.findElement(By.cssSelector("button[type=submit]")).click();
            wait.until(ExpectedConditions.urlContains("index"));
            System.out.println("✅ Login successful");

            driver.get("http://localhost/Ecommerce/FrontEnd/product.html");
            wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("searchBar")));
            driver.findElement(By.id("searchBar")).sendKeys("iphone");
            Thread.sleep(2000);

            System.out.println("✅ Search for 'iphone' completed");

            By addToCartBtn = By.cssSelector("button[onclick='addToCart(13)']");
            wait.until(ExpectedConditions.elementToBeClickable(addToCartBtn)).click();

            System.out.println("✅ 'Add to Cart' button clicked");

            driver.get("http://localhost/Ecommerce/FrontEnd/Cart.html");
            By cartProduct = By.cssSelector(".cart-item[data-product-id='13']");
            wait.until(ExpectedConditions.visibilityOfElementLocated(cartProduct));

            System.out.println("✅ Product is present in the cart");

        } catch (Exception e) {
            System.out.println("❌ Error occurred during automation:");
            e.printStackTrace();
        } finally {
            driver.quit();
        }
    }
}
