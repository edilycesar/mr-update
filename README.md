# mr-update
https://github.com/edilycesar/mr-update/archive/master.zip


[mrupdate-send-files]

Exemplo: php mrupdate-send-files.php -c mr-ftp-nfe-hosts.json


[mrupdate-send-db]

Envia queries SQL para os clientes da Lista de configurações (Veja documentação)

Comando: $php mrupdate-send-db.php

Parâmetros:
 -c : Lista de configurações;
 -q : Queries SQL;

Obs: Cada query SQL da lista de "queryes" deve estar em uma linha; 

Exemplo: php mrupdate-send-db.php -c mr-ftp-nfe-hosts.json -q query.sql


[mrupdate-del-bkps]

Exemplo: php mrupdate-del-bkps.php -c mr-ftp-nfe-hosts.json

Parâmetros:
 -c : Lista de configurações;
 -i : Ignorar algum item por data, Se você quiser apagar todas as pastas de backup.  
menos alguma, deverá indicar aqui a data dela no formato AAAA-MM-DD_HH-MM-SS (a data 
pode ser informada parcialmente, ex: -i AAAA-MM-DD).
ex: php mrupdate-del-bkps.php -i 2017-01-09_07-47-58;
 -g : Remove também lixo deixado por eventuar retorno de backup (mrupdate-back-bkp);


[Lista de configurações]

Descrição: Lista em formato json que conté parâmetros e configurações;

{
	"uConfig": { <== Configurações dos clientes
		"cli01": { 	    <== Divisão de cada Cliente (Pode haver mais de uma)
                        "name": "", <== Apelido
			"host": "", <== Host FTP
			"user": "", <== User FTP
			"pass": "", <== Pass FTP
			"port": "", <== Porta FTP
			"basePath": "/", <== Dir base FTP
			"folders": [], <== Caminho (a partir do basePath) das pastas que serão enviadas (Array);

                        "dbDriver": "", <== Driver do banco de dados (Só foi testado com MySQL)
                        "dbHost": "", <== Host Banco de dados
                        "dbUser": "", <== User Banco de dados
                        "dbPass": "", <== Senha Banco de dados
                        "dbName": "forjecom_nfe", <== Nome do Banco de dados
                        "dbCharset": "utf8", <== Charset Banco de dados
                        "dbCollation": "utf8_unicode_ci", <== Collation Banco de dados 
                        "dbPrefix": "" <== Prefixo do Banco de dados
		},
		"cli02": {                         
		}
	},

	"srcPaths": { <== Origem dos arquivos a serem enviados (nomes das pastas e caminho completo, Veja exemplo)
		
	}
}

Exemplo: 
======================= JSON ==========================
{
	"uConfig": {
		"nfe": { 	
                        "name": "Débora",
			"host": "ftp.forje.com.br",
			"user": "nfe@nfe.forje.com.br", 
			"pass": "P@SSW0RDFTP", 
			"port": "21", 
			"basePath": "/",
			"folders": ["app","public/css", "public/js"],
                        "dbDriver": "mysql",
                        "dbHost": "forje.com.br",
                        "dbUser": "forjecom_nfe",
                        "dbPass": "P@SSW0RDDB",
                        "dbName": "forjecom_nfe",
                        "dbCharset": "utf8", 
                        "dbCollation": "utf8_unicode_ci", 
                        "dbPrefix": ""
		},
		"mdl": { 
                        "name": "Yapa",
			"host": "ftp.forje.com.br",
			"user": "mdl@mdl.forje.com.br", 
			"pass": "P@SSW0RDFTP", 
			"port": "21", 
			"basePath": "/",
			"folders": ["app","public/css", "public/js"],
                        "dbDriver": "mysql",
                        "dbHost": "forje.com.br",
                        "dbUser": "forjecom_nfe_mdl",
                        "dbPass": "P@SSW0RDDB",
                        "dbName": "forjecom_nfe_mdl",
                        "dbCharset": "utf8", 
                        "dbCollation": "utf8_unicode_ci", 
                        "dbPrefix": ""
		}
	},

	"srcPaths": {
		"app":"/var/www/projects/project/app",
		"public/css": "/var/www/projects/project/public/css",
		"public/js" : "/var/www/projects/project/public/js"		
	}
}

[mrupdate-back-bkp]

Descrição: Recupera pastas de backup;

Parâmetros: 
 -s : Data da pasta de backup, Formato: AAAA-MM-DD_HH-MM-SS, pode ser passado percialmente, ex: AAAA-MM-DD.

Exemplo: php mrupdate-back-bkp.php -c mr-ftp-nfe.json -s 2017-01-09_08-






