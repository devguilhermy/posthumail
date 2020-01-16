
-- --------------------------------------------------------

--
-- Estrutura da tabela `recipient_message_link`
--

CREATE TABLE `recipient_message_link` (
  `message_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
