<?php

declare(strict_types=1);

namespace App\Enums\Pochta;

enum  PochtaTariffType: string
{
    // для этих необходимо указание конкретного цвпп
    // для этих необходимо указание конкретного типа упаковки
    case StandatrPackage = '27030';
    case StandatrPackageWithSumoc = '27020';
    case StandatrPackageWithSumocAndCash = '27040';

    // ! case ExpressPackage = '29030';
    // ! case ExpressPackageWithSumoc = '29020';
    // ! case ExpressPackageWithSumocAndCash = '29040';

    // ! case EmsCourier = '28030';
    // ! case EmsCourierWithSumoc = '28020';
    // ! case EmsCourierWithSumocAndCash = '28040';

    case UnstandartPackage = '4030';
    case UnstandartPackageWithSumoc = '4020';
    case UnstandartPackageWithSumocAndCash = '4040';

    case FirstClassPackage = '47030';
    case FirstClassPackageWithSumoc = '47020';
    case FirstClassPackageWithSumocAndCash = '47040';

    case OnlinePackage = '23030';
    case OnlinePackageWithSumoc = '23020';
    case OnlinePackageWithSumocAndCash = '23040';

    case EasyReturnpackage = '51030';
    case EasyReturnpackageWithSumoc = '51020';

    case OnlineCourier = '24030';
    case OnlineCourierWithSumoc = '24020';
    case OnlineCourierWithSumocAndCash = '24040';

    case BusinesCourier = '30030';
    case BusinesCourierWithSumoc = '30020';

    case BusinesCourierExpress = '31030';
    case BusinesCourierExpressWithSumoc = '31020';

    case KpoStandart = '39000';
    case KpoEconom = '40000';

    // ! case Ekom = '53030';
    // ! case EkomWithSumoc = '53070';

    case EkomMarketplace = '54020';

    case Ems = '7030';
    case EmsWithSumoc = '7020';
    case EmsWithSumocAndCash = '7040';

    // для этих необходимо указание конкретного цвпп
    case EmsOptium = '34030';
    case EmsOptiumWithSumoc = '34020';
    case EmsOptiumWithSumocAndCash = '34040';

    case EmsPt = '41030';
    case EmsPtWithSumoc = '41020';
    // ? case EmsPtWithSumocAndCash = '41040';

    case EmsTender = '52030';
    case EmsTenderWithSumoc = '52020';
    case EmsTenderWithSumocAndCash = '52060';

    // исходящие
    // этим нужен country-to по международному классификатору
    // сумма наложенного платежа не должна быть выше суммы объявленной ценности
    case PackageOutgoing = '4031';
    case PackageOutgoingWithSumoc = '4021';
    case PackageOutgoingWithSumocAndCash = '4041';

    case EmsOutgoing = '7031';

    // у этих вес не может превышать 2 кг
    case LitlePackageOutgoing = '5001';
    case LitlePackageOrderingOutgoing = '5011';

    case BagM = '9001';
    case BagMOrdering = '9011';

    // входящие
    // ! case PackageIncome = '4032';
    // ! case PackageIncomeWithSumoc = '4022';
    // ! case PackageIncomeWithSumocAndCas = '4042';

    // ! case EmsIncome = '7032';
    // ! case EmsIncomeWithSumoc = '7022';
    // ! case EmsIncomeWithSumocAndCas = '7042';

    // ! case LitlePackageIncome = '5002';
    // ! case LitlePackageOrderingIncome = '5012';

    public function attributes() {}


    public function name()
    {
        return match ($this) {
            self::StandatrPackage => 'Письмо простое', // 
            self::StandatrPackageWithSumoc => 'Посылка стандарт с объявленной ценностью',
            self::StandatrPackageWithSumocAndCash => 'Посылка стандарт с объявленной ценностью и наложенным платежом',

            // ! self::ExpressPackage => 'Посылка экспресс',
            // ! self::ExpressPackageWithSumoc => 'Посылка экспресс с объявленной ценностью',
            // ! self::ExpressPackageWithSumocAndCash => 'Посылка экспресс с объявленной ценностью и наложенным платежом',

            // ! self::EmsCourier => 'Посылка курьер EMS',
            // ! self::EmsCourierWithSumoc => 'Посылка курьер EMS с объявленной ценностью',
            // ! self::EmsCourierWithSumocAndCash => 'Посылка курьер EMS с объявленной ценностью и наложенным платежом',

            self::UnstandartPackage => 'Посылка нестандартная',
            self::UnstandartPackageWithSumoc => 'Посылка нестандартная с объявленной ценностью',
            self::UnstandartPackageWithSumocAndCash => 'Посылка нестандартная с объявленной ценностью и наложенным платежом',

            self::FirstClassPackage => 'Посылка 1 класса',
            self::FirstClassPackageWithSumoc => 'Посылка 1 класса с объявленной ценностью',
            self::FirstClassPackageWithSumocAndCash => 'Посылка 1 класса с объявленной ценностью и наложенным платежом',

            self::OnlinePackage => 'Посылка онлайн обыкновенная',
            self::OnlinePackageWithSumoc => 'Посылка онлайн с объявленной ценностью',
            self::OnlinePackageWithSumocAndCash => 'Посылка онлайн с объявленной ценностью и наложенным платежом',

            self::EasyReturnpackage => 'Посылка “Легкий возврат” обыкновенная',
            self::EasyReturnpackageWithSumoc => 'Посылка “Легкий возврат” с объявленной ценностью',

            self::OnlineCourier => 'Курьер онлайн обыкновенный',
            self::OnlineCourierWithSumoc => 'Курьер онлайн с объявленной ценностью',
            self::OnlineCourierWithSumocAndCash => 'Курьер онлайн с объявленной ценностью и наложенным платежом',

            self::BusinesCourier => 'Бизнес курьер',
            self::BusinesCourierWithSumoc => 'Бизнес курьер с объявленной ценностью',

            self::BusinesCourierExpress => 'Бизнес курьер экспресс',
            self::BusinesCourierExpressWithSumoc => 'Бизнес курьер экспресс с объявленной ценностью',

            self::KpoStandart => 'КПО-стандарт',
            self::KpoEconom => 'КПО-эконом',

            // ! self::Ekom => 'ЕКОМ',
            // ! self::EkomWithSumoc => 'ЕКОМ с объявленной ценностью',

            self::EkomMarketplace => 'ЕКОМ Маркетплейс с объявленной ценностью',

            self::Ems => 'EMS',
            self::EmsWithSumoc => 'EMS с объявленной ценностью',
            self::EmsWithSumocAndCash => 'EMS с объявленной ценностью и наложенным платежом',

            self::EmsOptium => 'EMS оптимальное',
            self::EmsOptiumWithSumoc => 'EMS оптимальное с объявленной ценностью',
            self::EmsOptiumWithSumocAndCash => 'EMS оптимальное с объявленной ценностью и наложенным платежом',

            self::EmsPt => 'EMS PT',
            self::EmsPtWithSumoc => 'EMS PT с объявленной ценностью',

            // ? self::EmsPtWithSumocAndCash => 'EMS PT с объявленной ценностью и наложенным платежом',

            self::EmsTender => 'EMS PT',
            self::EmsTenderWithSumoc => 'EMS PT с объявленной ценностью',
            self::EmsTenderWithSumocAndCash => 'EMS PT с объявленной ценностью и наложенным платежом',

            // исходящие
            self::PackageOutgoing => 'Посылка обыкновенная исходящая',
            self::PackageOutgoingWithSumoc => 'Посылка с объявленной ценностью исходящая',
            self::PackageOutgoingWithSumocAndCash => 'Посылка с объявленной ценностью и наложенным платежом исходящая',

            self::EmsOutgoing => 'EMS обыкновенное исходящее',

            self::LitlePackageOutgoing => 'Мелкий пакет простой исходящий',
            self::LitlePackageOrderingOutgoing => 'Мелкий пакет заказной исходящий',

            self::BagM => 'Мешок М простой исходящий',
            self::BagMOrdering => 'Мешок М заказной исходящий',

            // входящие
            // ! self::PackageIncome => 'Посылка обыкновенная входящая',
            // ! self::PackageIncomeWithSumoc => 'Посылка с объявленной ценностью входящая',
            // ! self::PackageIncomeWithSumocAndCas => 'Посылка с объявленной ценностью и наложенным платежом входящая',

            // ! self::EmsIncome => 'EMS обыкновенное входящее',
            // ! self::EmsIncomeWithSumoc => 'EMS с объявленной ценностью входящее',
            // ! self::EmsIncomeWithSumocAndCas => 'EMS с объявленной ценностью и наложенным платежом входящее',

            // ! self::LitlePackageIncome => 'Мелкий пакет простой входящий',
            // ! self::LitlePackageOrderingIncome => 'Мелкий пакет заказной входящий',
        };
    }
}
