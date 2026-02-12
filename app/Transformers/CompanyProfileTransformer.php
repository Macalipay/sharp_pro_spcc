<?php 
namespace App\Transformers;

use App\CompanyProfile;
use League\Fractal\TransformerAbstract;

class CompanyProfileTransformer extends TransformerAbstract
{
    public function transform(CompanyProfile $company)
    {
        return [
            'com_id__' => $company->id,
            'com_name__' => $company->company_name,
            'com_email__' => $company->email,
            'com_address__' => $company->address,
            'com_legal__' => $company->legal_name,
            'com_tin__' => $company->tin,
            'com_logo__' => $company->company_logo,
            'com_theme__' => $company->theme,
            'com_sv-by__' => $company->created_by,
            'com_up-by__' => $company->updated_by,
            'com_sv-date__' => $company->created_date,
            'com_up-date__' => $company->updated_date,
        ];
    }
}