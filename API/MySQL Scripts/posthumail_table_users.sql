
-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `confirmation_interval` int(10) UNSIGNED NOT NULL,
  `deadline` int(10) UNSIGNED NOT NULL,
  `last_confirmation` date NOT NULL DEFAULT current_timestamp(),
  `last_email` date NOT NULL DEFAULT current_timestamp(),
  `message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
