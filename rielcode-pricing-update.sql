-- Add 'Custom Plan' to orders.package enum
ALTER TABLE `orders`
MODIFY `package` enum('Student Plan','Starter Plan','Pro Plan','Premium Plan','Custom Plan') NOT NULL;
