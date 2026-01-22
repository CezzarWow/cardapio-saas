# QZ Tray Keys

Este diretório contém os arquivos de certificado e chave privada para o QZ Tray.

## Como gerar os arquivos

1. Abra o QZ Tray (deve estar instalado)
2. Execute no terminal/CMD:
   ```
   "C:\Program Files\QZ Tray\qz-tray.exe" --certgen
   ```
3. Copie os arquivos gerados para este diretório:
   - `digital-certificate.txt` (certificado público)
   - `private-key.pem` (chave privada)

## Segurança

⚠️ **IMPORTANTE**:
- O arquivo `private-key.pem` é CONFIDENCIAL
- Nunca exponha este arquivo publicamente
- O `.htaccess` nesta pasta bloqueia acesso direto aos arquivos
- Apenas os scripts PHP (`certificate.php` e `sign.php`) podem ler estes arquivos

## Estrutura esperada

```
/qz/keys/
├── .htaccess              ← Proteção de acesso
├── README.md              ← Este arquivo
├── digital-certificate.txt ← Certificado público (você precisa criar)
└── private-key.pem        ← Chave privada (você precisa criar)
```
