
-- --------------------------------------------------------

--
-- Estrutura da tabela `tokens`
--

CREATE TABLE `tokens` (
  `id` int(11) NOT NULL,
  `token_name` varchar(200) NOT NULL,
  `token_nature` varchar(50) NOT NULL,
  `client_id` int(11) NOT NULL,
  `expiration` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
