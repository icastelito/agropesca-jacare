<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProdutorRural;
use Faker\Factory as Faker;

class ProdutorRuralSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        // Nomes mais realistas e variados
        $primeiroNomes = [
            'João',
            'Maria',
            'José',
            'Ana',
            'Francisco',
            'Antônia',
            'Pedro',
            'Rosa',
            'Manuel',
            'Francisca',
            'Raimundo',
            'Luzia',
            'Carlos',
            'Terezinha',
            'Sebastião',
            'Conceição',
            'Benedito',
            'Rita',
            'Antônio',
            'Marlene',
            'Domingos',
            'Joana',
            'Vicente',
            'Zilda',
            'Manoel',
            'Neuza',
            'Geraldo',
            'Ivone',
            'Waldemar',
            'Edilene',
            'Paulo',
            'Luciana',
            'Edmundo',
            'Vera',
            'Alberto',
            'Sônia',
            'Fernando',
            'Regina',
            'Marcos',
            'Sandra',
        ];

        $sobrenomes = [
            'Silva',
            'Santos',
            'Oliveira',
            'Costa',
            'Pereira',
            'Sousa',
            'Ferreira',
            'Rodrigues',
            'Alves',
            'Martins',
            'Araújo',
            'Lima',
            'Carvalho',
            'Gomes',
            'Ribeiro',
            'Barbosa',
            'Cardoso',
            'Nascimento',
            'Teixeira',
            'Farias',
            'Cavalcante',
            'Mendes',
            'Moura',
            'Nunes',
            'Rocha',
            'Dias',
            'Castro',
        ];

        $complementosSobrenome = [
            'dos Santos',
            'de Oliveira',
            'da Silva',
            'de Sousa',
            'de Jesus',
            'das Chagas',
            'Nonato',
            'de Paula',
            'Henrique',
            'de Cássia',
        ];

        $razoesSociais = [
            'Agropecuária',
            'Fazenda',
            'Sítio',
            'Rancho',
            'Cooperativa',
            'Agroindústria',
            'Granja',
            'Laticínios',
            'Chácara',
            'Estância',
            'Haras',
            'Frigorífico',
        ];

        $complementosEmpresa = [
            'Boa Vista',
            'São José',
            'Santa Maria',
            'Vista Alegre',
            'Primavera',
            'Recanto Verde',
            'Buriti Dourado',
            'Sol Nascente',
            'Água Limpa',
            'Ipê Amarelo',
            'Três Marias',
            'Bela Vista',
            'Boa Sorte',
            'Palmital',
            'Aurora',
            'Esperança',
            'Progresso',
            'União',
            'São João',
            'Santa Clara',
        ];

        // Cidades de diversos estados brasileiros
        $cidades = [
            // Maranhão
            ['cidade' => 'São Luís', 'uf' => 'MA'],
            ['cidade' => 'Imperatriz', 'uf' => 'MA'],
            ['cidade' => 'Caxias', 'uf' => 'MA'],
            ['cidade' => 'Codó', 'uf' => 'MA'],
            ['cidade' => 'Açailândia', 'uf' => 'MA'],
            ['cidade' => 'Bacabal', 'uf' => 'MA'],
            ['cidade' => 'Balsas', 'uf' => 'MA'],
            ['cidade' => 'Santa Inês', 'uf' => 'MA'],

            // São Paulo
            ['cidade' => 'São Paulo', 'uf' => 'SP'],
            ['cidade' => 'Campinas', 'uf' => 'SP'],
            ['cidade' => 'Ribeirão Preto', 'uf' => 'SP'],
            ['cidade' => 'Piracicaba', 'uf' => 'SP'],
            ['cidade' => 'Bauru', 'uf' => 'SP'],
            ['cidade' => 'Presidente Prudente', 'uf' => 'SP'],
            ['cidade' => 'Araçatuba', 'uf' => 'SP'],

            // Minas Gerais
            ['cidade' => 'Belo Horizonte', 'uf' => 'MG'],
            ['cidade' => 'Uberlândia', 'uf' => 'MG'],
            ['cidade' => 'Uberaba', 'uf' => 'MG'],
            ['cidade' => 'Montes Claros', 'uf' => 'MG'],
            ['cidade' => 'Patos de Minas', 'uf' => 'MG'],
            ['cidade' => 'Passos', 'uf' => 'MG'],

            // Bahia
            ['cidade' => 'Salvador', 'uf' => 'BA'],
            ['cidade' => 'Feira de Santana', 'uf' => 'BA'],
            ['cidade' => 'Vitória da Conquista', 'uf' => 'BA'],
            ['cidade' => 'Barreiras', 'uf' => 'BA'],
            ['cidade' => 'Luís Eduardo Magalhães', 'uf' => 'BA'],
            ['cidade' => 'Ilhéus', 'uf' => 'BA'],

            // Paraná
            ['cidade' => 'Curitiba', 'uf' => 'PR'],
            ['cidade' => 'Londrina', 'uf' => 'PR'],
            ['cidade' => 'Maringá', 'uf' => 'PR'],
            ['cidade' => 'Cascavel', 'uf' => 'PR'],
            ['cidade' => 'Ponta Grossa', 'uf' => 'PR'],
            ['cidade' => 'Toledo', 'uf' => 'PR'],

            // Rio Grande do Sul
            ['cidade' => 'Porto Alegre', 'uf' => 'RS'],
            ['cidade' => 'Caxias do Sul', 'uf' => 'RS'],
            ['cidade' => 'Pelotas', 'uf' => 'RS'],
            ['cidade' => 'Santa Maria', 'uf' => 'RS'],
            ['cidade' => 'Passo Fundo', 'uf' => 'RS'],
            ['cidade' => 'Uruguaiana', 'uf' => 'RS'],

            // Goiás
            ['cidade' => 'Goiânia', 'uf' => 'GO'],
            ['cidade' => 'Rio Verde', 'uf' => 'GO'],
            ['cidade' => 'Jataí', 'uf' => 'GO'],
            ['cidade' => 'Catalão', 'uf' => 'GO'],
            ['cidade' => 'Itumbiara', 'uf' => 'GO'],

            // Mato Grosso
            ['cidade' => 'Cuiabá', 'uf' => 'MT'],
            ['cidade' => 'Rondonópolis', 'uf' => 'MT'],
            ['cidade' => 'Sinop', 'uf' => 'MT'],
            ['cidade' => 'Sorriso', 'uf' => 'MT'],
            ['cidade' => 'Lucas do Rio Verde', 'uf' => 'MT'],

            // Mato Grosso do Sul
            ['cidade' => 'Campo Grande', 'uf' => 'MS'],
            ['cidade' => 'Dourados', 'uf' => 'MS'],
            ['cidade' => 'Três Lagoas', 'uf' => 'MS'],
            ['cidade' => 'Maracaju', 'uf' => 'MS'],

            // Pará
            ['cidade' => 'Belém', 'uf' => 'PA'],
            ['cidade' => 'Santarém', 'uf' => 'PA'],
            ['cidade' => 'Marabá', 'uf' => 'PA'],
            ['cidade' => 'Castanhal', 'uf' => 'PA'],

            // Tocantins
            ['cidade' => 'Palmas', 'uf' => 'TO'],
            ['cidade' => 'Araguaína', 'uf' => 'TO'],
            ['cidade' => 'Gurupi', 'uf' => 'TO'],
        ];

        $bairros = [
            'Centro',
            'Vila Nova',
            'Cohab',
            'Jardim América',
            'São Francisco',
            'Santa Rosa',
            'Turu',
            'Renascença',
            'Cohama',
            'Anil',
            'Vinhais',
            'Bequimão',
            'Vila Lobão',
            'Sacavém',
            'São Raimundo',
            'Ponta da Areia',
            'São Cristóvão',
            'Calhau',
            'Olho d\'Água',
            'Maiobão',
            'Cidade Operária',
        ];

        $tiposLogradouro = ['Rua', 'Avenida', 'Travessa', 'Alameda', 'Rodovia'];

        $nomesRua = [
            'das Palmeiras',
            'dos Holandeses',
            'Grande',
            'do Sol',
            'Kennedy',
            'da Paz',
            'São João',
            'Colares Moreira',
            'Nina Rodrigues',
            'do Passeio',
            'Osvaldo Cruz',
            '7 de Setembro',
            'Getúlio Vargas',
            'Presidente Vargas',
            'São Pedro',
            'das Flores',
            'do Comércio',
            'Principal',
            'Central',
        ];

        echo "\n🌱 Criando 200 Produtores Rurais variados...\n\n";

        for ($i = 0; $i < 200; $i++) {
            $isPessoaFisica = $i < 130; // 65% PF, 35% PJ

            if ($isPessoaFisica) {
                // Pessoa Física - gera nome completo realista
                $primeiro = $primeiroNomes[array_rand($primeiroNomes)];
                $meio = $sobrenomes[array_rand($sobrenomes)];
                $ultimo = rand(0, 1) ? $sobrenomes[array_rand($sobrenomes)] : $complementosSobrenome[array_rand($complementosSobrenome)];
                $nome = "{$primeiro} {$meio} {$ultimo}";
                $cpfCnpj = $this->gerarCPF();
            } else {
                // Pessoa Jurídica
                $tipo = $razoesSociais[array_rand($razoesSociais)];
                $complemento = $complementosEmpresa[array_rand($complementosEmpresa)];
                $sufixos = ['Ltda', 'ME', 'S/A', 'EPP', 'EIRELI'];
                $sufixo = $sufixos[array_rand($sufixos)];
                $nome = "{$tipo} {$complemento} {$sufixo}";
                $cpfCnpj = $this->gerarCNPJ();
            }

            // Endereço completo e realista
            $tipoLog = $tiposLogradouro[array_rand($tiposLogradouro)];
            $nomeRua = $nomesRua[array_rand($nomesRua)];
            $numero = rand(1, 999);
            $bairro = $bairros[array_rand($bairros)];
            $cidadeInfo = $cidades[array_rand($cidades)];
            $endereco = "{$tipoLog} {$nomeRua}, {$numero}, {$bairro} - {$cidadeInfo['cidade']}/{$cidadeInfo['uf']}";

            // Email mais realista
            $email = '';
            if (rand(1, 100) > 25) { // 75% tem email
                $emailBase = $this->gerarEmailBase($nome);
                $dominios = ['gmail.com', 'hotmail.com', 'outlook.com'];
                $email = $emailBase . '@' . $dominios[array_rand($dominios)];
            }

            // Telefone com 9 dígitos (celular)
            $telefone = $this->gerarTelefone();

            ProdutorRural::create([
                'nome' => $nome,
                'cpf_cnpj' => $cpfCnpj,
                'telefone' => $telefone,
                'email' => $email,
                'endereco' => $endereco,
                'data_cadastro' => $faker->dateTimeBetween('-8 years', 'now'),
            ]);

            // Progresso visual
            if ((($i + 1) % 50) == 0) {
                $count = $i + 1;
                echo "   ✓ {$count} produtores criados...\n";
            }
        }

        $this->command->info("\n✅ 200 Produtores Rurais criados com sucesso!");
    }

    private function gerarEmailBase(string $nome): string
    {
        $nome = strtolower($this->removerAcentos($nome));
        $partes = explode(' ', $nome);

        // Diferentes formatos de email
        $formatos = [
            $partes[0] . '.' . ($partes[1] ?? 'silva'),                    // joao.silva
            $partes[0] . ($partes[count($partes) - 1] ?? ''),            // joaosantos
            substr($partes[0], 0, 1) . '.' . ($partes[count($partes) - 1] ?? 'silva'), // j.santos
            $partes[0] . rand(1, 999),                                     // joao123
            $partes[0] . '.' . substr($partes[count($partes) - 1] ?? 'silva', 0, 4), // joao.sant
        ];

        return str_replace(' ', '', $formatos[array_rand($formatos)]);
    }

    private function removerAcentos(string $string): string
    {
        $map = [
            'á' => 'a',
            'à' => 'a',
            'ã' => 'a',
            'â' => 'a',
            'ä' => 'a',
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'í' => 'i',
            'ì' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ó' => 'o',
            'ò' => 'o',
            'õ' => 'o',
            'ô' => 'o',
            'ö' => 'o',
            'ú' => 'u',
            'ù' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ç' => 'c',
            'ñ' => 'n',
        ];

        return str_replace(array_keys($map), array_values($map), $string);
    }

    private function gerarCPF(): string
    {
        $n = array_map(fn() => rand(0, 9), range(1, 9));
        $d1 = 11 - (($n[0] * 10 + $n[1] * 9 + $n[2] * 8 + $n[3] * 7 + $n[4] * 6 + $n[5] * 5 + $n[6] * 4 + $n[7] * 3 + $n[8] * 2) % 11);
        $d1 = $d1 >= 10 ? 0 : $d1;
        $d2 = 11 - (($n[0] * 11 + $n[1] * 10 + $n[2] * 9 + $n[3] * 8 + $n[4] * 7 + $n[5] * 6 + $n[6] * 5 + $n[7] * 4 + $n[8] * 3 + $d1 * 2) % 11);
        $d2 = $d2 >= 10 ? 0 : $d2;
        return implode('', $n) . $d1 . $d2;
    }

    private function gerarCNPJ(): string
    {
        $n = array_merge(array_map(fn() => rand(0, 9), range(1, 8)), [0, 0, 0, 1]);
        $d1 = 11 - (($n[0] * 5 + $n[1] * 4 + $n[2] * 3 + $n[3] * 2 + $n[4] * 9 + $n[5] * 8 + $n[6] * 7 + $n[7] * 6 + $n[8] * 5 + $n[9] * 4 + $n[10] * 3 + $n[11] * 2) % 11);
        $d1 = $d1 >= 10 ? 0 : $d1;
        $d2 = 11 - (($n[0] * 6 + $n[1] * 5 + $n[2] * 4 + $n[3] * 3 + $n[4] * 2 + $n[5] * 9 + $n[6] * 8 + $n[7] * 7 + $n[8] * 6 + $n[9] * 5 + $n[10] * 4 + $n[11] * 3 + $d1 * 2) % 11);
        $d2 = $d2 >= 10 ? 0 : $d2;
        return implode('', $n) . $d1 . $d2;
    }

    private function gerarTelefone(): string
    {
        $ddds = ['98', '99']; // DDDs do Maranhão
        $ddd = $ddds[array_rand($ddds)];
        $nono = 9; // Celular sempre começa com 9
        $resto = rand(10000000, 99999999);
        return $ddd . $nono . $resto;
    }
}
