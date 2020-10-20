<?php
require_once __DIR__ . '/vendor/autoload.php';


class XlsExchange
{
    protected string $path_to_input_json_file;
    protected string $path_to_output_xlsx_file;
    protected string $ftp_host;
    protected string $ftp_login;
    protected string $ftp_password;
    protected string $ftp_dir;


    /**
     * @param string $path
     * @return $this
     * @throws Exception
     */
    public function setInputFile(string $path): self
    {
        if (false === file_exists($path)) {
            throw new Exception('Input file does not exist');
        }

        $this->path_to_input_json_file = $path;

        return $this;
    }


    /**
     * @param string $path
     * @return $this
     */
    public function setOutputFile(string $path): self
    {
        $this->path_to_output_xlsx_file = $path;

        return $this;
    }


    /**
     * @param string $host
     * @param string $login
     * @param string $password
     * @param string $uploadDir
     * @return $this
     */
    public function setFtpConnectionData(string $host, string $login, string $password, string $uploadDir): self
    {
        $this->ftp_host     = $host;
        $this->ftp_login    = $login;
        $this->ftp_password = $password;
        $this->ftp_dir      = $uploadDir;
        return $this;
    }


    /**
     * @return bool
     */
    public function export(): bool
    {
        $data = $this->jsonFileHandler($this->path_to_input_json_file);

        if (!empty($data['items'])) {

            $items = [];

            foreach ($data['items'] as $item) {
                $item = [
                    'id' => $item['id'],
                    'barcode' => $item['item']['barcode'],
                    'name' => $item['item']['name'],
                    'amount' => $item['amount'],
                    'summ' => $item['amount'] * $item['price'],

                ];

                if (false === $this->isValidBarcode($item['barcode'])) {
                    continue;
                }

                $items[] = $item;
            }



            if (!empty($items)) {

                $exportFileData = $items;

                $headerTitles = [
                    'Id', 'ШК', 'Название', 'Кол-во', 'Сумма'
                ];

                array_unshift($exportFileData, $headerTitles);

                $xlsx = SimpleXLSXGen::fromArray( $exportFileData );

                return $xlsx->saveAs('items.xlsx');

            }
        }

        return false;
    }

    protected function getItems(array $data): array
    {
        //TODO getItems
        return [];

    }



    protected function isValidBarcode(string $barcode): bool
    {

        //TODO isValidBarcode
        return true;

    }

    protected function exportToFtpServer()
    {

        //TODO exportToFtpServer
        $file = "/home/user/myfile";

        /* соединение с сервером */
        $connId = ftp_connect($this->ftp_host);
        $loginResult = ftp_login($connId, $this->ftp_login, $this->ftp_password);

        if ($loginResult) {

            ftp_put($connId, '/incoming/myfile', $file, FTP_BINARY);
        }

        ftp_close($connId);

    }


    /**
     * @param string $path
     * @return mixed
     */
    protected function jsonFileHandler(string $path)
    {
        return json_decode(file_get_contents($path), true);
    }

}