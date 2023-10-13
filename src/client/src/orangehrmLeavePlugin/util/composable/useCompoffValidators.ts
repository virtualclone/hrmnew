import { APIService } from '@/core/util/services/api.service';

interface CompOffModel {
    applyDate: Date;
    durationLabel: string;
}

interface ParamsObj {
    applyDate: Date | string | undefined | null;
    durationLabel: string | null |undefined;
}

interface OverlapObj {
    isConflict: boolean;
    isOverWorkshift: boolean;
    data: Array<object>;
}

export default function useCompOffValidators(http: APIService) {

    const serializeParams = (compensatory: CompOffModel) => {
        const payload: ParamsObj = {
            applyDate: compensatory.applyDate ? new Date(compensatory.applyDate).toISOString().split('T')[0] : null,
            durationLabel: compensatory.durationLabel,
        };
        return payload;
    };   


    const validateOverlapCompoff = (
        compensatory: CompOffModel
    ): Promise<OverlapObj> => {
        // console.log(compensatory)
        serializeParams(compensatory)
        return new Promise((resolve, reject) => {
            http
                .request({
                    method: 'GET',
                    url: `/api/v2/leave/compensatory-off/${compensatory.applyDate}/${compensatory.durationLabel}`,
                    params: serializeParams(compensatory),
                })
                .then((response) => {

                    const { data, meta } = response.data
                    const result = Object.keys(data).map((key) => [key,data[key]]);
                    console.log(result);
                    if (result.length > 0) {
                        resolve({
                            isConflict: true,
                            isOverWorkshift: meta.isWorkShiftLengthExceeded === true,
                            data,
                        });
                    } else {
                        resolve({
                            isConflict: false,
                            isOverWorkshift: false,
                            data: [],
                        });
                    }
                })
                .catch((error) => {
                    reject(error);
                });
        });
    };

    return {
        validateOverlapCompoff,
    };
}
