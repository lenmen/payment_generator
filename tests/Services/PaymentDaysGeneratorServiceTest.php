<?php


namespace App\Tests\Services;

use App\Services\PaymentDaysGeneratorService;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class PaymentDaysGeneratorServiceTest extends TestCase {
    /**
     * @test
     */
    public function it_returns_the_last_day_of_the_month_if_its_a_workday() {
        $paymentDayGenerator = new PaymentDaysGeneratorService();
        $paymentDate = $paymentDayGenerator->getNormalSalaryDate(Carbon::parse('2019-01'));

        $this->assertEquals(31, $paymentDate->format('d'));

    }

    /**
     * @test
     */
    public function it_returns_wednesday_before_the_last_day_of_the_normal_salary() {
        $paymentDayGenerator = new PaymentDaysGeneratorService();
        $paymentDate = $paymentDayGenerator->getNormalSalaryDate(Carbon::parse('2019-03'));

        $this->assertEquals(27, $paymentDate->format('d'));
    }

    /**
     * @test
     */
    public function it_returns_the_bonus_date_on_the_15th() {
        $paymentDayGenerator = new PaymentDaysGeneratorService();
        $paymentDate = $paymentDayGenerator->getBonusSalaryDate(Carbon::parse('2019-03'));

        $this->assertEquals(15, $paymentDate->format('d'));
    }

    /**
     * @test
     */
    public function it_returns_the_bonus_date_on_wednesday_after_the_15th() {
        $paymentDayGenerator = new PaymentDaysGeneratorService();
        $paymentDate = $paymentDayGenerator->getBonusSalaryDate(Carbon::parse('2019-06'));

        $this->assertEquals(19, $paymentDate->format('d'));
    }
}