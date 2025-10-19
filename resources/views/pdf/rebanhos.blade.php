<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Rebanhos - {{ $produtor->nome }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2c5282;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 20px;
            color: #2c5282;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 11px;
            color: #666;
        }

        .produtor-info {
            background-color: #f7fafc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #2c5282;
        }

        .produtor-info h2 {
            font-size: 14px;
            color: #2c5282;
            margin-bottom: 10px;
        }

        .produtor-info p {
            margin-bottom: 5px;
            font-size: 11px;
        }

        .produtor-info strong {
            color: #2c5282;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table thead {
            background-color: #2c5282;
            color: white;
        }

        table th {
            padding: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }

        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }

        table tbody tr:nth-child(even) {
            background-color: #f7fafc;
        }

        table tbody tr:hover {
            background-color: #edf2f7;
        }

        .totalizadores {
            background-color: #f7fafc;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .totalizadores h3 {
            font-size: 14px;
            color: #2c5282;
            margin-bottom: 10px;
        }

        .totalizadores-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .totalizador-item {
            background-color: white;
            padding: 10px;
            border-radius: 3px;
            border-left: 3px solid #2c5282;
        }

        .totalizador-item .label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }

        .totalizador-item .valor {
            font-size: 16px;
            font-weight: bold;
            color: #2c5282;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Relatório de Rebanhos por Produtor</h1>
        <p>Sistema de Gestão Agropecuária - Jacaré dos Homens/AL</p>
    </div>

    <div class="produtor-info">
        <h2>Dados do Produtor Rural</h2>
        <p><strong>Nome:</strong> {{ $produtor->nome }}</p>
        <p><strong>CPF/CNPJ:</strong> {{ $produtor->cpf_cnpj }}</p>
        <p><strong>Telefone:</strong> {{ $produtor->telefone }}</p>
        <p><strong>E-mail:</strong> {{ $produtor->email }}</p>
        <p><strong>Total de Propriedades:</strong> {{ $produtor->propriedades->count() }}</p>
    </div>

    @if($rebanhos->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Propriedade</th>
                <th>Município/UF</th>
                <th>Espécie</th>
                <th>Quantidade</th>
                <th>Finalidade</th>
                <th>Última Atualização</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rebanhos as $item)
            <tr>
                <td>{{ $item['propriedade']->nome }}</td>
                <td>{{ $item['propriedade']->municipio }}/{{ $item['propriedade']->uf }}</td>
                <td>{{ $item['rebanho']->especie }}</td>
                <td>{{ number_format($item['rebanho']->quantidade, 0, ',', '.') }}</td>
                <td>{{ $item['rebanho']->finalidade }}</td>
                <td>{{ $item['rebanho']->data_atualizacao->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totalizadores">
        <h3>Totalizadores</h3>
        <div class="totalizadores-grid">
            <div class="totalizador-item">
                <div class="label">Total de Animais</div>
                <div class="valor">{{ number_format($total_animais, 0, ',', '.') }}</div>
            </div>

            @foreach($total_por_especie as $especie => $quantidade)
            <div class="totalizador-item">
                <div class="label">{{ $especie }}</div>
                <div class="valor">{{ number_format($quantidade, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="no-data">
        <p>Nenhum rebanho cadastrado para este produtor rural.</p>
    </div>
    @endif

    <div class="footer">
        <p>Relatório gerado em {{ $data_geracao }}</p>
        <p>Sistema Agropesca Jacaré - Todos os direitos reservados</p>
    </div>
</body>

</html>