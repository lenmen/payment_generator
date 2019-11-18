<?php

namespace App\Tests\Services;

use App\Kernel;
use App\Services\PaymentDaysSpreadsheetService;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use Psr\Log\LoggerInterface;
use Roromix\Bundle\SpreadsheetBundle\Factory;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class PaymentDaysSpreadsheetServiceTest extends TestCase {
    /**
     * @test
     */
    public function it_sets_the_field_names() {
        $factory = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $spreadSheet = $this->getMockBuilder(Spreadsheet::class)->disableOriginalConstructor()->getMock();
        $worksSheet = $this->getMockBuilder(Worksheet::class)->disableOriginalConstructor()->getMock();
        $kernel = $this->getMockBuilder(Kernel::class)->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $factory->expects($this->once())
            ->method('createSpreadsheet')
            ->willReturn($spreadSheet);

        $spreadSheet->expects($this->once())
            ->method('getActiveSheet')
            ->willReturn($worksSheet);

        $worksSheet->expects($this->at(0))
            ->method('setCellValue')
            ->with('A1', 'Month')
            ->willReturn($worksSheet);

        $worksSheet->expects($this->at(1))
            ->method('setCellValue')
            ->with('B1', 'Salary date')
            ->willReturn($worksSheet);

        $worksSheet->expects($this->at(2))
            ->method('setCellValue')
            ->with('C1', 'Bonus date')
            ->willReturn($worksSheet);

        new PaymentDaysSpreadsheetService($factory, $kernel, $logger);
    }

    /**
     * @test
     */
    public function it_returns_true_when_a_month_has_been_added() {
        $factory = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $spreadSheet = $this->getMockBuilder(Spreadsheet::class)->disableOriginalConstructor()->getMock();
        $worksSheet = $this->getMockBuilder(Worksheet::class)->disableOriginalConstructor()->getMock();
        $kernel = $this->getMockBuilder(Kernel::class)->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $factory->expects($this->once())
            ->method('createSpreadsheet')
            ->willReturn($spreadSheet);

        $spreadSheet->expects($this->any())
            ->method('getActiveSheet')
            ->willReturn($worksSheet);

        $worksSheet->method('setCellValue')
            ->willReturn($worksSheet);

        $paymentDaysSpreadSheetService = new PaymentDaysSpreadsheetService($factory, $kernel, $logger);

        $worksSheet->expects($this->at(0))
            ->method('setCellValue')
            ->with('A2', 'January')
            ->willReturn($worksSheet);

        $worksSheet->expects($this->at(1))
            ->method('setCellValue')
            ->with('B2', '15-12-2019')
            ->willReturn($worksSheet);

        $worksSheet->expects($this->at(2))
            ->method('setCellValue')
            ->with('C2', '23-12-2019')
            ->willReturn($worksSheet);


        $this->assertTrue($paymentDaysSpreadSheetService->addMonth(
            0,
            'January',
            Carbon::parse('15-12-2019'),
            Carbon::parse('23-12-2019')
        ));
    }

    /**
     * @test
     */
    public function it_returns_false_when_it_fails_to_add_a_month() {
        $factory = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $spreadSheet = $this->getMockBuilder(Spreadsheet::class)->disableOriginalConstructor()->getMock();
        $worksSheet = $this->getMockBuilder(Worksheet::class)->disableOriginalConstructor()->getMock();
        $kernel = $this->getMockBuilder(Kernel::class)->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $factory->expects($this->once())
            ->method('createSpreadsheet')
            ->willReturn($spreadSheet);

        $spreadSheet->expects($this->at(0))
            ->method('getActiveSheet')
            ->willReturn($worksSheet);

        $worksSheet->method('setCellValue')
            ->willReturn($worksSheet);

        $paymentDaysSpreadSheetService = new PaymentDaysSpreadsheetService($factory, $kernel, $logger);
        $worksheetException = new Exception('failed');

        $spreadSheet->expects($this->at(0))
            ->method('getActiveSheet')
            ->willThrowException($worksheetException);

        $logger->expects($this->once())
            ->method('error')
            ->with('Failed to add month to the worksheet', [
                'month' => 'January',
                'bonus_date' => '23-12-2019',
                'salary_date' => '15-12-2019',
                'exception' => $worksheetException,
            ]);

        $this->assertFalse($paymentDaysSpreadSheetService->addMonth(
            0,
            'January',
            Carbon::parse('15-12-2019'),
            Carbon::parse('23-12-2019')
        ));
    }

    /**
     * @test
     */
    public function it_returns_true_when_the_spread_sheet_has_been_saved() {
        $factory = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $spreadSheet = $this->getMockBuilder(Spreadsheet::class)->disableOriginalConstructor()->getMock();
        $worksSheet = $this->getMockBuilder(Worksheet::class)->disableOriginalConstructor()->getMock();
        $kernel = $this->getMockBuilder(Kernel::class)->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $writer = $this->getMockBuilder(IWriter::class)->disableOriginalConstructor()->getMock();

        $factory->expects($this->once())
            ->method('createSpreadsheet')
            ->willReturn($spreadSheet);

        $spreadSheet->method('getActiveSheet')
            ->willReturn($worksSheet);

        $worksSheet->method('setCellValue')
            ->willReturn($worksSheet);

        $paymentDaysSpreadSheetService = new PaymentDaysSpreadsheetService($factory, $kernel, $logger);

        $factory->expects($this->once())
            ->method('createWriter')
            ->with($spreadSheet, 'Csv')
            ->willReturn($writer);

        $kernel->expects($this->once())
            ->method('getProjectDir')
            ->willReturn('.');

        $writer->expects($this->once())
            ->method('save')
            ->with('./payments/test.csv')
            ->willReturn(true);

        $this->assertTrue($paymentDaysSpreadSheetService->saveSpreadSheet('test.csv'));
    }

    /**
     * @test
     */
    public function it_returns_false_when_the_file_could_not_be_saved() {
        $factory = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $spreadSheet = $this->getMockBuilder(Spreadsheet::class)->disableOriginalConstructor()->getMock();
        $worksSheet = $this->getMockBuilder(Worksheet::class)->disableOriginalConstructor()->getMock();
        $kernel = $this->getMockBuilder(Kernel::class)->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $writer = $this->getMockBuilder(IWriter::class)->disableOriginalConstructor()->getMock();

        $factory->expects($this->once())
            ->method('createSpreadsheet')
            ->willReturn($spreadSheet);

        $spreadSheet->method('getActiveSheet')
            ->willReturn($worksSheet);

        $worksSheet->method('setCellValue')
            ->willReturn($worksSheet);

        $paymentDaysSpreadSheetService = new PaymentDaysSpreadsheetService($factory, $kernel, $logger);
        $exception = new \PhpOffice\PhpSpreadsheet\Writer\Exception('failed');

        $factory->expects($this->once())
            ->method('createWriter')
            ->with($spreadSheet, 'Csv')
            ->willReturn($writer);

        $kernel->expects($this->once())
            ->method('getProjectDir')
            ->willReturn('.');

        $writer->expects($this->once())
            ->method('save')
            ->with('./payments/test.csv')
            ->willThrowException($exception);

        $logger->expects($this->once())
            ->method('error')
            ->with('Failed to save the spreadsheet', [
               'file' => './payments/test.csv',
                'exception' => $exception,
            ]);

        $this->assertFalse($paymentDaysSpreadSheetService->saveSpreadSheet('test.csv'));
    }
}