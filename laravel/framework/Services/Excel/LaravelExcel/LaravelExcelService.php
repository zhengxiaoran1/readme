<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/3/2
 * Time: 20:01
 */
namespace Framework\Services\Excel\LaravelExcel;

class LaravelExcelService
{
    private $fileType = [
        'xls', 'xlsx', 'csv'
    ];

    /**
     * Excel文件导出功能
     * @author Sojo
     * @param string $fileName 文件名
     * @param array $cellData 需要导出的内容，格式：
     * <pre>
     *  [
     *      ['学号', '姓名', '成绩'],
     *      ['10001', 'AAAAA', '99'],
     *      ['10002', 'BBBBB', '92'],
     *      ['10003', 'CCCCC', '95'],
     *      ['10004', 'DDDDD', '89'],
     *      ['10005', 'EEEEE', '96']
     *  ]
     * </pre>
     * @param string $ext 扩展名
     * @param bool $isServerSave
     */
    public function export($fileName, $cellData, $ext = 'xlsx', $isServerSave = false)
    {
        if (!in_array($ext, $this->fileType)) $ext = 'xlsx';

        $export = \Excel::create($fileName, function ($excel) use ($cellData) {
            $excel->sheet('score', function ($sheet) use ($cellData) {
                $sheet->rows($cellData);
            });
        });

        if ($isServerSave) $export->store($ext);

        $export->export($ext);
    }

    /**
     * Excel文件导入功能
     * @author Sojo
     * @param string $file 文件
     * @param null|string $path 路径
     * @return object
     */
    public function import($file, $path = null)
    {
        $filePath = storage_path('exports/' . $file);
        if (!empty($path) && is_string($path)) $filePath = $path . $file;

        return \Excel::load($filePath);
    }
}