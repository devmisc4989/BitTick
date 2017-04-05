<!DOCTYPE html >
<html >
    <head>
        <script type="text/javascript">
            (function () {
                var redir = document.location.href.indexOf('?') === -1 ? document.location.href + '?' : document.location.href;
                redir = redir.replace('protocol=ssl', '');
                var protocolIsSsl = '<?= $editorProtocolIsHttps ?>' === 'TRUE' ? true : false;
                if('<?= $notSecureButSslOn ?>' === 'TRUE') {
                    redir = redir.replace('https://', 'http://');
                    protocolIsSsl = false;
                }
                if('<?= $secureButSslOff ?>' === 'TRUE') {
                    redir = redir.replace('http://', 'https://');
                    protocolIsSsl = true;
                }
                redir += '<?= $sessionNoRedirect ?>' === 'TRUE' ? '&redir=yes' : '';
                redir += protocolIsSsl ? '&protocol=ssl' : '';
                top.location = redir;
            })();
        </script>
    </head>
    <body>...</body>
</html>