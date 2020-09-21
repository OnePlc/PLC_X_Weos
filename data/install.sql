SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `weos_order` (
  `Order_ID` int(11) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_complete` datetime NOT NULL,
  `user_idfs` int(11) NOT NULL,
  `deliverydate_start` datetime NOT NULL,
  `deliverydate_end` datetime NOT NULL,
  `category_idfs` INT(11) NOT NULL DEFAULT '0',
  `contact_idfs` int(11) NOT NULL,
  `reminder` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `weos_order`
  ADD PRIMARY KEY (`Order_ID`);


ALTER TABLE `weos_order`
  MODIFY `Order_ID` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
