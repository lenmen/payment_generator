<?php
/**
 * Created by PhpStorm.
 * User: lennardmoll
 * Date: 27/01/2019
 * Time: 20:00
 */

namespace App\Services;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Exception;
use Psr\Log\LoggerInterface;
use Roromix\Bundle\SpreadsheetBundle\Factory;
use Symfony\Component\HttpKernel\Kernel;

class PaymentDaysSpreadsheetService {
    /**
     * @var \Roromix\Bundle\SpreadsheetBundle\Factory
     */
    private $factory;

    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    private $kernel;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    private $spreadsheet;



    /**
     * @param \Roromix\Bundle\SpreadsheetBundle\Factory $factory
     * @param \Symfony\Component\HttpKernel\Kernel      $kernel
     * @param \Psr\Log\LoggerInterface                  $logger
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function __construct(Factory $factory, Kernel $kernel, LoggerInterface $logger) {
        $this->factory = $factory;
        $this->kernel = $kernel;
        $this->logger = $logger;
        $this->spreadsheet = $factory->createSpreadsheet();

        $this->setFieldNames();

    }

    /**
     * @param int            $monthNumber
     * @param string         $month
     * @param \Carbon\Carbon $salaryDate
     * @param \Carbon\Carbon $bonusDate
     *
     * @return bool
     */
    public function addMonth(
        int $monthNumber,
        string $month,
        Carbon $salaryDate,
        Carbon $bonusDate
    ) : bool {
        $position = $monthNumber + 2;
        $salary = $salaryDate->format('d-m-Y');
        $bonus = $bonusDate->format('d-m-Y');

        try {
            $this->spreadsheet->getActiveSheet()
                ->setCellValue('A' . $position, $month)
                ->setCellValue('B' . $position, $salary)
                ->setCellValue('C' . $position, $bonus);

            return true;
        } catch(Exception $exception) {
            $this->logger->error('Failed to add month to the worksheet', [
                'month' => $month,
                'bonus_date' => $bonus,
                'salary_date' => $salary,
                'exception' => $exception,
            ]);
        }

        return false;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function setFieldNames() : void {
        $this->spreadsheet->getActiveSheet()
            ->setCellValue('A1', 'Month')
            ->setCellValue('B1', 'Salary date')
            ->setCellValue('C1', 'Bonus date');
    }

    /**
     * @param string $filename
     *
     * @return bool
     */
    public function saveSpreadSheet(string $filename) : bool {
        $file = sprintf('%s/payments/%s', $this->kernel->getProjectDir(), $filename);

        try {
            $this->factory->createWriter($this->spreadsheet, 'Csv')->save($file);

            return true;
        } catch(\Exception $exception) {
            $this->logger->error('Failed to save the spreadsheet', [
                'file' => $file,
                'exception' => $exception,
            ]);
        }

        return false;
    }
}
