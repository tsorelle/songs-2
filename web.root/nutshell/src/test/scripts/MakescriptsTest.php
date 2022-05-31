<?php

namespace PeanutTest\scripts;

use Tops\db\TQuery;
use function GuzzleHttp\Psr7\str;

class MakescriptsTest extends TestScript
{

    private $oldtables = [
        '`scymorg_application`.',
        '`agegroups`',
        '`attenders`',
        '`charges`',
        '`credits`',
        '`credittypes`',
        '`days`',
        '`donations`',
        '`donationtypes`',
        '`feetypes`',
        '`generations`',
        '`housingassignments`',
        '`housingtypes`',
        '`housingunits`',
        '`meals`',
        '`payments`',
        '`persons`',
        '`registrations`',
        '`registrationstatustypes`',
        '`specialneedstypes`',
        '`youths`',
        '`annualsessions`',
        '`meetings`',
        '`quarterlymeetings`',
        '`affiliationcodes`',
                '`HousingAssignmentCounts`',
                '`accountLookupsView`',
                '`arrivalTimeSummaryView`',
                '`attenderCountsView`',
                '`attenderMeetingView`',
                '`attendersReportView`',
                '`attendersView`',
                '`balancesView`',
                '`campReportDownloadView`',
                '`campReportHousingDetailView`',
                '`chargesView`',
                '`creditsReportView`',
                '`creditsView`',
                '`currentAttenderCountsView`',
                '`currentAttenders`',
                '`currentAttendersReportView`',
                '`currentHousingAssignments`',
                '`currentLedgerDetailView`',
                '`currentLedgerView`',
                '`currentRegistrationsView`',
                '`donationsView`',
                '`financeSummaryDownloadView`',
                '`financialAidReportView`',
                '`housingAssignmentCountsReportView`',
                '`housingAssignmentCountsView`',
                '`housingAssignmentsTextView`',
                '`housingAvailabilityView`',
                '`housingCountsDetailView`',
                '`housingRequestCountsReportView`',
                '`housingRosterView`',
                '`housingTypesView`',
                '`housingUnitsView`',
                '`incomeView`',
                '`lastyearAttenders`',
                '`ledgerDetailView`',
                '`ledgerDownloadView`',
                '`ledgerView`',
                '`mealCountsView`',
                '`mealRosterReportView`',
                '`membershipview`',
                '`nameTagsDownloadView`',
                '`occupantsView`',
                '`paymentsView`',
                '`registrarsReportView`',
                '`registrationCountsDownloadView`',
                '`registrationsReceivedReportView`',
                '`subsidiesReportView`',
                '`temp_attenderMeetingView`',
                '`temp_attending`',
                '`tempview`',
                '`testview`',
                '`transactionsDownloadView`',
                '`youthView`'
            ];

    private $newtables = [
        '', // database name removed
        '`scym_agegroups`',
        '`scym_attenders`',
        '`scym_charges`',
        '`scym_credits`',
        '`scym_credittypes`',
        '`scym_days`',
        '`scym_donations`',
        '`scym_donationtypes`',
        '`scym_feetypes`',
        '`scym_generations`',
        '`scym_housingassignments`',
        '`scym_housingtypes`',
        '`scym_housingunits`',
        '`scym_meals`',
        '`scym_payments`',
        '`qnut_persons`',
        '`scym_registrations`',
        '`scym_registrationstatustypes`',
        '`scym_specialneedstypes`',
        '`scym_youths`',
        '`qnut_sessions`',
        '`qnut_organizations`',
        '`qnut_quarterlies`',
        '`qnut_organizations`',
        '`scym_view_housingassignmentcounts`',
        '`scym_view_accountlookups`',
        '`scym_view_arrivaltimesummary`',
        '`scym_view_attendercounts`',
        '`scym_view_attendermeeting`',
        '`scym_view_attendersreport`',
        '`scym_view_attenders`',
        '`scym_view_balances`',
        '`scym_view_campreportdownload`',
        '`scym_view_campreporthousingdetail`',
        '`scym_view_charges`',
        '`scym_view_creditsreport`',
        '`scym_view_credits`',
        '`scym_view_currentattendercounts`',
        '`scym_view_currentattenders`',
        '`scym_view_currentattendersreport`',
        '`scym_view_currenthousingassignments`',
        '`scym_view_currentledgerdetail`',
        '`scym_view_currentledger`',
        '`scym_view_currentregistrations`',
        '`scym_view_donations`',
        '`scym_view_financesummarydownload`',
        '`scym_view_financialaidreport`',
        '`scym_view_housingassignmentcountsreport`',
        '`scym_view_housingassignmentcounts`',
        '`scym_view_housingassignmentstext`',
        '`scym_view_housingavailability`',
        '`scym_view_housingcountsdetail`',
        '`scym_view_housingrequestcountsreport`',
        '`scym_view_housingroster`',
        '`scym_view_housingtypes`',
        '`scym_view_housingunits`',
        '`scym_view_income`',
        '`scym_view_lastyearattenders`',
        '`scym_view_ledgerdetail`',
        '`scym_view_ledgerdownload`',
        '`scym_view_ledger`',
        '`scym_view_mealcounts`',
        '`scym_view_mealrosterreport`',
        '`scym_view_membership`',
        '`scym_view_nametagsdownload`',
        '`scym_view_occupants`',
        '`scym_view_payments`',
        '`scym_view_registrarsreport`',
        '`scym_view_registrationcountsdownload`',
        '`scym_view_registrationsreceivedreport`',
        '`scym_view_subsidiesreport`',
        '`scym_view_temp_attendermeeting`',
        '`scym_view_temp_attending`',
        '`scym_view_temp`',
        '`scym_view_test`',
        '`scym_view_transactionsdownload`',
        '`scym_view_youth`'
    ];

    public function execute()
    {

        $lines = [];
        $query = new TQuery();
        $views = $query->getAll('SELECT viewname,source FROM migrate_views WHERE processed <> 1');
        $count = 0;
        foreach ($views as $view) {
            if ($count) {
                $lines[] = "\n\n";
            }
            $count++;
            $viewname = 'scym_view_'.strtolower(str_replace('View','',$view->viewname));

            $newSource = str_replace($this->oldtables,$this->newtables,$view->source);
            $lines[] = '-- DROP VIEW IF EXISTS '.$viewname.";\n";
            $lines[] = '-- CREATE VIEW '.$viewname.' AS '."\n";
            $lines[] = $newSource.";\n";;
        }

        $outputPath = 'D:\dev\scym2021\sql\views\views.sql';
        if (file_exists($outputPath)) {
            unlink($outputPath);
        }
        file_put_contents($outputPath,$lines);

    }
}