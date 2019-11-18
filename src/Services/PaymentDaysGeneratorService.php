<?php


namespace App\Services;

use Carbon\Carbon;

class PaymentDaysGeneratorService {
    /**
     * @param \Carbon\Carbon $date
     *
     * @return \Carbon\Carbon
     */
    public function getNormalSalaryDate(Carbon $date) : Carbon {
        $endOfMonth = $date->endOfMonth();

        if ($endOfMonth->isWeekend() === true) {
            return $date->previous(Carbon::WEDNESDAY);
        }

        return $endOfMonth;
    }

    /**
     * @param \Carbon\Carbon $date
     *
     * @return \Carbon\Carbon
     */
    public function getBonusSalaryDate(Carbon $date) : Carbon {
        $bonusDate =  $date->firstOfMonth()->addDays(14);

        if ($bonusDate->isWeekend() === true) {
            return $bonusDate->next(Carbon::WEDNESDAY);
        }

        return $bonusDate;
    }
}
