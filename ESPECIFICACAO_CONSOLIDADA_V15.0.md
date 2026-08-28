# SIGA V15.0 — Especificação Consolidada de Requisitos

Este documento é a fonte de verdade funcional para a linha V15.x. Decisões aqui registadas não podem ser alteradas ou esquecidas sem decisão explícita.

## Base de dados
- Motor: MySQL/MariaDB.
- Charset: `utf8mb4`.
- Collation: `utf8mb4_uca1400_as_ci`.
- V15.0 é instalação de raiz com um único script de criação.

## Pessoas e associados
- `pessoas` é a entidade base para dados pessoais.
- `associados` guarda os dados específicos da condição de associado.
- Género: Masculino, Feminino e Outro.
- Para género Outro, o nominativo é introduzido manualmente.
- Nome do pai e nome da mãe fazem parte dos dados do associado.
- Estado civil, confissão religiosa e tipo de documento de identificação são tabelas de referência.
- Número de utente: `CHAR(9)`, sempre tratado como texto e preservando zeros à esquerda.

## Cartão de Cidadão
- O número é tratado como texto, nunca como inteiro.
- Zeros à esquerda não podem ser perdidos.
- Quando aplicável à regra definida para o campo, o preenchimento é completado automaticamente à esquerda com zeros.
- A largura exacta deve ser mantida de forma consistente no modelo, validação e interface; não pode ser inventada ou alterada numa release sem decisão explícita.

## Datas
- Interface: `dd/mm/aaaa`.
- Base de dados: `YYYY-MM-DD`.
- A introdução deve inserir automaticamente as barras para permitir `01011969` -> `01/01/1969`.
- Data de nascimento não pode ser futura.
- Data de inscrição e data de evento sugerem a data actual, permitem datas anteriores e não permitem datas futuras.

## Eventos
- Eventos nunca são eliminados.
- A admissão gera automaticamente o evento `Admissão`.
- A data do evento de admissão corresponde à data de inscrição seleccionada.
- Tipos de evento são geridos no backoffice; `Admissão` existe nos dados iniciais.

## Estado do associado
- Inactivação não elimina o associado nem o histórico.
- Reactivação preserva o histórico.
- Reactivação não obriga automaticamente à associação a uma companhia.

## Moradas
- Moradas são entidades independentes e podem ser partilhadas.
- Deve ser possível corrigir uma morada existente; a correcção afecta todos os que a partilham.
- Deve também ser possível criar uma nova morada para substituir a anterior.
- As ligações devem permitir histórico por período de vigência.
- A Chefia Nacional também pode mudar de morada.

## Relações, encarregados e contactos
- Existe uma tabela de tipos de relação/parentesco, incluindo Cônjuge.
- Encarregados de educação têm entidade própria e relação com histórico.
- Contactos são generalizados.
- Cada associado pode ter um ou mais contactos de emergência.
- Um contacto de emergência pode ser uma pessoa que não esteja registada no SIGA.

## Dados iniciais
A criação de raiz deve incluir os dados iniciais aplicáveis, incluindo:
- nacionalidades internacionais;
- estados civis;
- confissões religiosas;
- tipos de documento de identificação;
- tipos de contacto;
- tipos de relação;
- secções;
- Chefia Nacional;
- tipo de evento Admissão;
- utilizador inicial Administrador.

## Distribuição
O pacote V15.0 deve conter código e exactamente um script SQL de criação de raiz. Não contém scripts de migração. A partir da V15.1, cada versão inclui também a migração da versão imediatamente anterior.
