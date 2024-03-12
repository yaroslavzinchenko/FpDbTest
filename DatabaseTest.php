<?php

namespace FpDbTest;

use Exception;

class DatabaseTest
{
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    public function testBuildQuery(): void
    {
        $results = [];

        $results[] = $this->db->buildQuery('SELECT name FROM users WHERE user_id = 1');

        $results[] = $this->db->buildQuery(
            'SELECT * FROM users WHERE name = ? AND block = 0',
            ['Jack']
        );

        $results[] = $this->db->buildQuery(
            'SELECT ?# FROM users WHERE user_id = ?d AND block = ?d',
            [['name', 'email'], 2, true]
        );

        $results[] = $this->db->buildQuery(
            'UPDATE users SET ?a WHERE user_id = -1',
            [['name' => 'Jack', 'email' => null]]
        );

        // Здесь есть особенность. Так как метод skip возвращает в текущем случае int, и когда мы передаём в buildQuery
        // с условием $block = null, то сначала происходит подстановка под ?d целого числа, а потом уже идёт проверка
        // на наличие специального значения внутри скобок. В данном случае, так как skip возвращает int, а внутри
        // блока идёт подстановка значения типа int, то всё работает. Если же skip вернет string, то запрос соберётся
        // неправильно. Но только если skip вернёт именно строку, а не int в строке. Можно подумать над тем, чтобы
        // возвращать значения разных типов в зависимости от того, какой спецификатор был передан.
        // С другой стороны, в задании написано: "Если внутри условного блока есть хотя бы один параметр со специальным
        // значением, то блок не попадает в сформированный запрос.". То есть в данном случае, как я понял, мы должны
        // сначала подставить значение в параметр, а потом произвести проверку на специальное значение, что сейчас и
        // происходит.
        // Я оставил возврат интового значения из метода skip.
        foreach ([null, true] as $block) {
            $results[] = $this->db->buildQuery(
                'SELECT name FROM users WHERE ?# IN (?a){ AND block = ?d}',
                ['user_id', [1, 2, 3], $block ?? $this->db->skip()]
            );
        }

        $correct = [
            'SELECT name FROM users WHERE user_id = 1',
            'SELECT * FROM users WHERE name = \'Jack\' AND block = 0',
            'SELECT `name`, `email` FROM users WHERE user_id = 2 AND block = 1',
            'UPDATE users SET `name` = \'Jack\', `email` = NULL WHERE user_id = -1',
            'SELECT name FROM users WHERE `user_id` IN (1, 2, 3)',
            'SELECT name FROM users WHERE `user_id` IN (1, 2, 3) AND block = 1',
        ];

        if ($results !== $correct) {
            throw new Exception('Failure.');
        }
    }
}
