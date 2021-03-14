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

CREATE TABLE `contact_zipcity` (
  `zip` int(4) NOT NULL,
  `contact_idfs` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `contact_zipcity`
  ADD PRIMARY KEY (`zip`,`contact_idfs`);

INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES
('index', 'OnePlace\\Weos\\Controller\\BookingController', 'Buchungen Übersicht', 'Bookings', '/booking', '1', '0'),
('zip', 'OnePlace\\Weos\\Controller\\ApiController', 'PLZ Suchen', '', '', '0', '0'),
('list', 'OnePlace\\Weos\\Controller\\WebController', 'Dienstleister auflisten', '', '', '0', '0'),
('calendar', 'OnePlace\\Weos\\Controller\\BookingController', 'Buchungen Kalender', 'Kalender', '/booking/calendar', '1', '0'),
('calendar', 'OnePlace\\Weos\\Controller\\BookingController', 'Buchungen Kalender', 'Kalender', '/booking/calendar', '1', '0'),
('slots', 'OnePlace\\Weos\\Controller\\BookingController', 'Buchungen Slots', '', '', '0', '0'),
('timeslots', 'OnePlace\\Weos\\Controller\\ApiController', 'Kalender TimeSlots anzeigen', '', '', '0', '0'),
('addslot', 'OnePlace\\Weos\\Controller\\BookingController', 'Slot erfassen', '', '', '0', '0'),
('confirm', 'OnePlace\\Weos\\Controller\\BookingController', 'Buchung bestätigen', '', '', '0', '0');


INSERT INTO `core_form_button` (`Button_ID`, `label`, `icon`, `title`, `href`, `class`, `append`, `form`, `mode`, `filter_check`, `filter_value`) VALUES
(NULL, 'Slots', 'fas fa-calendar-alt', 'Slots', '/booking/slots', 'primary', '', 'weos-booking-index', 'link', '', ''),
(NULL, 'Add Slot', 'fas fa-plus', 'Add Slot', '/booking/addslot', 'primary', '', 'weos-booking-slots', 'link', '', '');

INSERT INTO `settings` (`settings_key`, `settings_value`) VALUES ('calendar-timeslots', '1');

INSERT INTO `core_widget` (`Widget_ID`, `widget_name`, `label`, `permission`) VALUES
(NULL, 'weos_booking-requests', 'WEOS - Buchungsanfragen', 'calendar-OnePlace\\Weos\\Controller\\BookingController');

COMMIT;
