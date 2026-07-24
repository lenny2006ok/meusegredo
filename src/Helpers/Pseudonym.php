<?php
namespace MeuSegredo\Helpers;

class Pseudonym {
    private static $animals = [
        'Lobo', 'Lua', 'Raposa', 'Coruja', 'Águia', 'Pomba', 'Gato',
        'Fênix', 'Dragão', 'Cavalo', 'Andarilho', 'Sombra', 'Nuvem',
        'Folha', 'Estrela', 'Tigre', 'Pantera', 'Leão', 'Cervo', 'Lince',
        'Falcão', 'Garça', 'Gavião', 'Harpia', 'Jaguar', 'Javali'
    ];

    private static $adjectives = [
        'Cinza', 'Azul', 'Vermelho', 'Negra', 'Branca', 'Dourada',
        'Prata', 'Bronze', 'Esmeralda', 'Rubi', 'Safira', 'Jade',
        'Prateada', 'Sedosa', 'Veloz', 'Feroz', 'Misteriosa', 'Solene',
        'Nobre', 'Ardente', 'Glacial', 'Noturna', 'Solar', 'Lunar',
        'Silenciosa', 'Sábia', 'Valente', 'Majestosa', 'Eterna'
    ];

    private static $suffixes = ['4581', '7293', '8347', '5521', '9182', '3764'];

    public static function generate() {
        $animal = self::$animals[array_rand(self::$animals)];
        $adjective = self::$adjectives[array_rand(self::$adjectives)];

        if (rand(1, 100) <= 70) {
            return $animal . ' ' . $adjective;
        } else {
            return 'Anônimo ' . self::$suffixes[array_rand(self::$suffixes)];
        }
    }
}
