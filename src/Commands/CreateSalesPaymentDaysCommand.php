<?php

namespace App\Commands;

use App\Services\PaymentDaysGeneratorService;
use App\Services\PaymentDaysSpreadsheetService;
use Carbon\Carbon;
use Roromix\Bundle\SpreadsheetBundle\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSalesPaymentDaysCommand extends Command {
    const ARGUMENT_FILENAME = 'output_filename';

    /**
     * @var \App\Services\PaymentDaysGeneratorService
     */
    private $paymentDaysGeneratorService;


    /**
     * @var \App\Services\PaymentDaysSpreadsheetService
     */
    private $paymentDaysWorksheetService;

    /**
     * CreateSalesPaymentDaysCommand constructor.
     *
     * @param \App\Services\PaymentDaysGeneratorService   $paymentDaysGeneratorService
     * @param \App\Services\PaymentDaysSpreadsheetService $paymentDaysWorksheetService
     * @param null|string                                 $name
     */
    public function __construct(
        PaymentDaysGeneratorService $paymentDaysGeneratorService,
        PaymentDaysSpreadsheetService $paymentDaysWorksheetService,
        ?string $name = null
    ) {
        parent::__construct($name);

        $this->paymentDaysGeneratorService = $paymentDaysGeneratorService;
        $this->paymentDaysWorksheetService = $paymentDaysWorksheetService;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output) {
        $paymentMonths = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];

        array_walk($paymentMonths, function ($paymentMonth, $key) use ($output) {
            $output->writeln(sprintf('Processing month %s', $paymentMonth));
            $date = Carbon::createFromFormat('F', $paymentMonth);

            $normalSalaryDate = $this->paymentDaysGeneratorService->getNormalSalaryDate($date->copy());
            $output->writeln(sprintf('Normal payment date will be %s', $normalSalaryDate->format('d-m-Y')));

            $bonusSalaryDate = $this->paymentDaysGeneratorService->getBonusSalaryDate($date->copy());
            $output->writeln(sprintf('Bonus payment date will be %s', $bonusSalaryDate->format('d-m-Y')));

            $addMonth = $this->paymentDaysWorksheetService
                ->addMonth($key, $paymentMonth, $normalSalaryDate, $bonusSalaryDate);

            if($addMonth === false) {
                $output->writeln(sprintf('Failed to add month %s', $paymentMonth));
                return;
            }

            $output->writeln(sprintf('Month %s added to the worksheet', $paymentMonth));
        });

        $file = $this->paymentDaysWorksheetService->saveSpreadSheet($input->getArgument(self::ARGUMENT_FILENAME));

        if($file === false) {
            $output->writeln('Failed to save the spreadsheet');
            return;
        }

        $output->writeln('Spreadsheet created successfully!');

    }

    /**
     * {@inheritdoc}
     */
    public function configure() : void {
        $this->setDescription('generate payments days of the department sales')
            ->setName('generate:sales_payment_days')
            ->addArgument(
                self::ARGUMENT_FILENAME,
                InputArgument::OPTIONAL,
                'Where to store the dates',
                'sales_payment_days.csv'
            );
    }
}
