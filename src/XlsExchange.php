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


    /**
     * XlsExchange constructor.
     * @param null $ftpHost
     * @param null $ftpLogin
     * @param null $ftpPassword
     * @param null $ftpDir
     */
    public function __construct($ftpHost = null, $ftpLogin = null, $ftpPassword = null, $ftpDir = null)
    {
        $this->ftp_host     = $ftpHost;
        $this->ftp_login    = $ftpLogin;
        $this->ftp_password = $ftpPassword;
        $this->ftp_dir      = $ftpDir;
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

        if (false === is_readable( $path )) {
            throw new Exception( 'Input file is not readable' );
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
     * @return bool
     */
    public function export(): bool
    {
        //Получение данных из файла
        $data  = $this->jsonFileHandler( $this->path_to_input_json_file );

        //Подготовка данных для экспорта
        $items = $this->prepareDataItems( $data );

        if (empty( $items )) {
            return false;
        }

        $exportFileData = $items;

        //Названия столбцов для таблицы
        $headerTitles = [
            'Id', 'ШК', 'Название', 'Кол-во', 'Сумма'
        ];

        array_unshift( $exportFileData, $headerTitles );

        //Преобразование подготовленных данных для выгрузки в xlsx
        $xlsxData = SimpleXLSXGen::fromArray( $exportFileData );

        //Попытка выгрузить на удаленный сервер
        if (false === $this->exportToFtpServer( $xlsxData )) {

            //Выгрузка на локальный сервер
            return $this->exportToLocalServer( $xlsxData );
        }

        return true;
    }


    /**
     * @param string $path
     * @return mixed
     */
    protected function jsonFileHandler(string $path)
    {
        return json_decode( file_get_contents( $path ), true) ;
    }


    /**
     * @param array $data
     * @return array
     */
    protected function prepareDataItems(array $data): array
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

            if (false === Barcode::isValid( $item['barcode'] )) {
                continue;
            }

            $items[] = $item;
        }

        return $items;
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
        $streamContext = stream_context_create( $streamOptions );
        $filePointer = @fopen( $ftpFilePath, 'wt', 0, $streamContext );

        if (false === $filePointer) {
            return false;
        }

        $isFileCreated = fwrite( $filePointer, $xlsxString );
        fclose( $filePointer );

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

}
