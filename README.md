# Desenvolvimento de projeto geiser-müller

<img src="https://github.com/marcelomaurin/GIESER/blob/main/imgs/fatecrp.JPG">
-- FATEC - Ribeirão Preto --

## Objetivo do trabalho
Desenvolvimento de projeto prático na disciplina: Projetos de Equipamentos Médico-Hospitalares.
Este projeto, visa simular produto conceito, aferindo etapas no desenvolvimento de um projeto de equipamento médico hospitalar.

E projeto Sistemas Microcontrolados em Equipamentos Médico-Hospitalares

## Prof. Orientador nas disciplinas
Prof. Dr. MARCELO CAETANO OLIVEIRA ALVES - Sistemas Microcontrolados em Equipamentos Médico-Hospitalares 
Prof. Dr. ALESSANDRO FRAGA FARAH - Projetos de Equipamentos Médico-Hospitalares

## Participantes na disciplina projeto de equipamento médico hospitalar
- Marcelo Maurin Martins

## Participantes na disciplina Sistemas Microcontrolados em Equipamentos Médico-Hospitalares

## Visão de Negógio
O monitoramento de ambientes médicos hospitalares, auxilia na profilaxia no trato de doenças.
Em especial o pessoal de ambientes médicos, devido ao contato continuo com aparelhos que emitem radiação, podem no decorrer de anos, desenvolver patologias especificas de tal exposição.
O monitoramento dos ambientes, através da medição continua, auxilia aos gestores garantir que a exposição de seus colaboradores, estarão dentro de taxas seguras. Medindo acumuladamente, as taxas em locais e pessoas durante o ciclo de trabalho.

Segundo artigo: <a href="https://doi.org/10.9771/cmbio.v14i2.11658">[Medeiros, Caroline,Dorow  Patricia F;Brandão, Cristiane P;Ribeiro, Mariana R; Análise do conhecimento sobre radiações ionizantes e qualidade do equipamento de proteção individual em um hospital público]</a> a prática médica do diagnóstico por imagem requer controle de qualidade e proteção radiológica para assegurar segurança dos trabalhadores e usuários ocupacionalmente expostos.

Este projeto visa criar ferramental para monitorar tanto temperatura, humidade e níveis de radiação na faixa de 20mR/h ate 120 mR/h  (200uSv/h - 1200uSv/h)  de raios gama e 100mR/h até 1800 mR/h de raios Beta.

### Normativas

A Portaria Nº 453/98 da Secretaria de Vigilância Sanitária do Ministério da Saúde. Essa portaria estabelece os limites de dose individual anual e dose coletiva anual para os trabalhadores expostos à radiação ionizante. Os valores máximos permitidos de dose individual anual para trabalhadores ocupacionalmente expostos variam de 20 mSv (milisieverts) para partes do corpo que não são críticas (como braços e pernas) a 50 mSv para as partes críticas do corpo (como o olho e o sistema reprodutor). O valor máximo permitido de dose coletiva anual para trabalhadores expostos é de 1 mSv multiplicado pela idade do trabalhador em anos.

Para o público em geral, os valores máximos permitidos de dose efetiva anual são de 1 mSv (milisievert) em condições normais e de 5 mSv em situações de emergência. Os limites para exposição à radiação em instalações médicas também estão estabelecidos na Portaria Nº 453/98, que define valores máximos permitidos para radiações ionizantes em áreas públicas, áreas restritas e áreas controladas de instalações médicas.

- Norma CNEN-NN-3.01: Estabelece os requisitos básicos de segurança radiológica para instalações médicas, incluindo requisitos para monitoramento de radiação e proteção radiológica.
- Norma CNEN-NE-3.05: Estabelece os requisitos para o uso de fontes seladas em instalações médicas, incluindo requisitos para proteção radiológica, segurança física e operação segura das fontes.
- Norma ABNT NBR 14854: Estabelece os requisitos de segurança para a proteção contra radiações ionizantes em ambientes médicos.
- Norma ISO 4037: Estabelece os requisitos para os instrumentos de medição de radiação utilizados para monitoramento de radiação em ambientes médicos.
- Norma IEC 60601-1-3: Estabelece os requisitos de segurança para equipamentos eletromédicos relacionados à proteção contra radiação ionizante.****

## Aplicações
Monitoramento de radiação em ambientes de tratamento médico, como salas de radioterapia e laboratórios de radiofarmácia.

Monitoramento de radiação em pacientes que receberam tratamento com radioisótopos, para garantir a segurança do pessoal médico e da equipe que os atende.

Monitoramento de radiação em ambientes de pesquisa médica, como laboratórios de biologia molecular e de genética, onde há o uso de radioisótopos.

Monitoramento de radiação em ambientes de diagnóstico, como salas de raios X, tomografia computadorizada e ressonância magnética, para garantir que os pacientes e a equipe médica estejam seguros.

Controle de qualidade em equipamentos médicos que usam fontes de radiação, como aceleradores lineares e câmaras de radiografia.

Monitoramento de radiação em ambientes de produção de radioisótopos e de radiofármacos para uso médico, para garantir a segurança dos trabalhadores e do público em geral.

Monitoramento de radiação em ambientes de armazenamento e transporte de materiais radioativos para uso médico, para garantir que os regulamentos de segurança sejam seguidos.

Com a adição de um PC, o sensor de mesa pode ser integrado a sistemas de monitoramento em tempo real, permitindo que a equipe médica monitore continuamente os níveis de radiação em ambientes de trabalho ou em pacientes, permitindo uma resposta imediata caso haja um aumento nos níveis de radiação. Além disso, o PC pode ser usado para armazenar dados e gerar relatórios para fins de documentação e auditoria.

## Comparativo Comercial
Neste tópico abordamos equipamentos equivalentes, ao projeto apresentado.

### Dosímetro pessoal: 
O dosímetro pessoal é um dispositivo portátil usado para medir a exposição individual dos trabalhadores à radiação ionizante. 
Há diversos modelos disponíveis no mercado brasileiro, como o dosímetro DMC 3000 da Mirion Technologies e o dosímetro EPD-Mk2 da Ludlum Measurements.
### Monitor de área: 
O monitor de área é um dispositivo usado para monitorar os níveis de radiação em uma determinada área. 
Há diversos modelos disponíveis no mercado brasileiro, como o monitor de radiação GQ-320 Plus da GQ Electronics e o monitor de radiação RDS-31 da Thermo Fisher Scientific.
### Contador Geiger: 
O contador Geiger é um dispositivo portátil usado para medir a quantidade de radiação ionizante presente em uma área específica. 
Há diversos modelos disponíveis no mercado brasileiro, como o contador Geiger GMC-300E Plus da GQ Electronics e o contador Geiger Inspector Alert da S.E. International.

## Partes do Projeto
Abordaremos a visão das partes do projeto, apresentando o que cada parte faz

### Leitor Desktop
Leitor permite conectar diretamente o equipamento a um PC, realizando o monitoramento do ambiente, passando as informações para o PC.

### Leitor Wifi
Leitor autonomo conecta-se diretamente no wifi da instituição, passando os dados lidos através para o software de Gestão.

### Software de Gestão de Monitoramento
Conecta todos os equipamentos, permitindo registrar os valores coletados e obter informações estatísticas do ambiente.

### App de Monitoramento
Conecta no software, permitindo ao Gestor o acompanhamento real dos níveis, bem como alarmando possiveis exposições


## Etapas do Projeto

### Macro cronograma do projeto
- Contrução de protótipo simples de validação de conceito
- Aferição das informações baseado em equipamentos calibrados, garantindo que os dados lidos
- Construção de demais itens
- Validações e Testes de projeto
- Homologação de produto

# Documentação técnica
<a href= "https://github.com/marcelomaurin/GIESER/wiki">https://github.com/marcelomaurin/GIESER/wiki</a>

# Status Report de atividades (NEWS)
05/03/2023 - Montagem de protótipo parcialmente funcional.





