<?php
namespace App;

use SimpleXLSXGen;
use Exception;

class XlsExchange
{
    protected $path_to_input_json_file;
    protected $path_to_output_xlsx_file;
    protected $ftp_host;
    protected $ftp_login;
    protected $ftp_password;
    protected $ftp_dir;

    public function __construct($ftpHost = null, $ftpLogin = null, $ftpPassword = null, $ftpDir = null)
    {
        $this->ftp_host = $ftpHost;
        $this->ftp_login = $ftpLogin;
        $this->ftp_password = $ftpPassword;
        $this->ftp_dir = $ftpDir;
    }

    /**
     * @param string $path
     * @return $this
     * @throws Exception
     */
    public function setInputFile(string $path): self
    {
        if (false === file_exists( $path )) {
            throw new Exception( 'Input file does not exist' );
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
        $data  = $this->jsonFileHandler( $this->path_to_input_json_file );
        $items = $this->getItems( $data );

        if (empty( $items )) {
            return false;
        }

        $exportFileData = $items;
        $headerTitles = [
            'Id', 'ШК', 'Название', 'Кол-во', 'Сумма'
        ];

        array_unshift( $exportFileData, $headerTitles );

        $xlsxData = SimpleXLSXGen::fromArray( $exportFileData );

        if (false === $this->exportToFtpServer( $xlsxData )) {
            return $this->exportToLocalServer( $xlsxData );
        }

        return true;
    }


    /**
     * @param array $data
     * @return array
     */
    protected function getItems(array $data): array
    {
        $items = [];

        if (empty( $data['items'] )) {
            return $items;
        }

        foreach ($data['items'] as $item) {
            $item = [
                'id' => $item['id'],
                'barcode' => $item['item']['barcode'],
                'name' => $item['item']['name'],
                'amount' => $item['amount'],
                'sum' => $item['amount'] * $item['price'],

            ];

            if (false === $this->isValidBarcode( $item['barcode'] )) {
                continue;
            }

            $items[] = $item;
        }

        return $items;

    }


    /**
     * @param string $barcode
     * @return bool
     */
    protected function isValidBarcode(string $barcode): bool
    {
        $barcode = (string) $barcode;

        if (!preg_match( "/^[0-9]+$/", $barcode )) {
            return false;
        }

        if (13 !== strlen( $barcode )) {
            return false;
        }

        //get check digit
        $check    = substr( $barcode, -1 );
        $barcode  = substr( $barcode, 0, -1 );
        $sumEven = $sumOdd = 0;
        $even     = true;

        while(strlen( $barcode ) > 0) {

            $digit = substr( $barcode, -1 );

            if($even) {
                $sumEven += 3 * $digit;
            } else {
                $sumOdd += $digit;
            }

            $even = !$even;
            $barcode = substr( $barcode, 0, -1 );
        }

        $sum = $sumEven + $sumOdd;
        $sumRoundedUp = ceil($sum/10) * 10;

        return (floatval($check) == ($sumRoundedUp - $sum));

    }


    /**
     * @param SimpleXLSXGen $xlsxData
     * @return bool
     */
    protected function exportToFtpServer(SimpleXLSXGen $xlsxData): bool
    {

        $xlsxString = $xlsxData->__toString();
        $ftpFilePath = 'ftp://' . $this->ftp_login . ':' . $this->ftp_password . '@' . $this->ftp_host . '/' . $this->ftp_dir;
        $streamOptions = [
            'ftp' => [
                'overwrite' => true
            ]
        ];
        $streamContext = stream_context_create($streamOptions);
        $filePointer = fopen( $ftpFilePath, 'wt', 0, $streamContext );

        if (false === $filePointer) {
            return false;
        }

        $isFileCreated = fwrite ( $filePointer, $xlsxString );
        fclose ( $filePointer );

        return $isFileCreated;

    }


    /**
     * @param SimpleXLSXGen $xlsxData
     * @return bool
     */
    protected function exportToLocalServer(SimpleXLSXGen $xlsxData): bool
    {

        return $xlsxData->saveAs( $this->path_to_output_xlsx_file );

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
