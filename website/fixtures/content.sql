--
-- Dumping data for table `address`
--

INSERT INTO `address` (`address_id`, `address_label`, `address_street_1`, `address_street_2`, `address_neighborhood`, `address_city`, `address_state`, `address_region`, `address_country`, `address_postal_code`, `address_landmarks`, `address_contact_name`, `address_contact_email`, `address_contact_phone`, `address_latitude`, `address_longitude`, `address_active`, `address_created`, `address_updated`) VALUES
(1, 'My Home', '123 Sesame Street', NULL, NULL, 'White Plains', NULL, NULL, 'US', '12345', NULL, 'John Doe', NULL, NULL, 0.00000000, 0.00000000, 1, '2019-02-12 15:35:47', '2019-02-12 15:35:47');

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_banner`, `category_title`, `category_slug`, `category_summary`, `category_detail`, `category_tags`, `category_active`, `category_created`, `category_updated`) VALUES
(1, 'https://image.freepik.com/free-photo/healthy-lifestyle-background-with-alarm-clock-jump-rope_1428-1424.jpg', 'Lifestyle', 'lifestyle', 'A lifestyle blog is best defined as a digital content representation of its author’s everyday life and interests. ', 'A lifestyle blog is best defined as a digital content representation of its author’s everyday life and interests. A lifestyle blogger creates content inspired and curated by their personal interests and daily activities.', '[\"life\", \"style\"]', 1, '2019-02-03 11:28:03', '2019-02-03 11:28:03');

--
-- Dumping data for table `comment`
--

INSERT INTO `comment` (`comment_id`, `comment_rating`, `comment_detail`, `comment_active`, `comment_created`, `comment_updated`) VALUES
(1, 4.0, '**Congrats!**', 1, '2019-02-03 11:30:58', '2019-02-03 11:30:58');

--
-- Dumping data for table `comment_profile`
--

INSERT INTO `comment_profile` (`comment_id`, `profile_id`) VALUES
(1, 1);

--
-- Dumping data for table `file`
--

INSERT INTO `file` (`file_id`, `file_name`, `file_description`, `file_data`, `file_active`, `file_created`, `file_updated`) VALUES
(1, 'lifestyle.jpg', 'This is Lifestyle', 'https://image.freepik.com/free-photo/healthy-lifestyle-background-with-alarm-clock-jump-rope_1428-1424.jpg', 1, '2019-02-03 11:29:47', '2019-02-03 11:29:47');


--
-- Dumping data for table `post`
--

INSERT INTO `post` (`post_id`, `post_banner`, `post_title`, `post_slug`, `post_summary`, `post_detail`, `post_tags`, `post_meta`, `post_published`, `post_active`, `post_created`, `post_updated`, `post_public`, `post_status`, `post_comments`) VALUES
(1, 'https://image.freepik.com/free-photo/healthy-lifestyle-background-with-alarm-clock-jump-rope_1428-1424.jpg', 'Hello World', 'hello-world', 'Welcome to Incept. This is your first post. Edit or delete it, then start writing!', 'Welcome to Incept. This is your first post. Edit or delete it, then start writing!', '[\"hello world\"]', NULL, '2019-02-01 12:00:00', 1, '2019-02-03 10:33:07', '2019-02-04 02:32:47', 1, 'approved', 1);

--
-- Dumping data for table `post_category`
--

INSERT INTO `post_category` (`post_id`, `category_id`) VALUES
(1, 1);

--
-- Dumping data for table `post_comment`
--

INSERT INTO `post_comment` (`post_id`, `comment_id`) VALUES
(1, 1);

--
-- Dumping data for table `post_file`
--

INSERT INTO `post_file` (`post_id`, `file_id`) VALUES
(1, 1);

--
-- Dumping data for table `post_profile`
--

INSERT INTO `post_profile` (`post_id`, `profile_id`) VALUES
(1, 1);

--
-- Dumping data for table `profile_address`
--

INSERT INTO `profile_address` (`profile_id`, `address_id`) VALUES
(1, 1);
