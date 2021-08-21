<?php
namespace common;
use common\aop\AopClient;

class AliPay{
    private $aop;
    private $notifyUrl;
    private $appId;
    private $rsaPrivateKey;
    private $alipayrsaPublicKey;

    public function __construct($pay_version = '')
    {
        $this->aop = new AopClient;
        $this->aop->format = "json";
        $this->aop->postCharset = "UTF-8";
        $this->aop->signType = "RSA2";

        if ('prod' == YII_ENV) {
            //xijinpro
            if($pay_version == 'ios_v2'){  // 无语
                $this->aop->appId = '2021001164691116';
                $this->aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
                $this->aop->rsaPrivateKey = 'MIIEowIBAAKCAQEAhIi/X/UeaWtUOMpGKXpc62TppVhCxSq1xNykkgOu9t0HNY4oFVCAsjbQjqAewyTQfLZMqeKmbP6VluKW8YERoGer7HymS4XzMi1FJub5ckzHesgnyN8SLK3rh+0IiIqKlzUzunY+um44mvd5TyHDXEzc/m9o8oHipPc9UT+ixLV842ZDFP+m4yC6+9RH5GKgLL4onmbhH3Ee7B9wSB5h/86REXanfRByNA0VdtpjotMbhht3Eswbaf43Wahi1xmT6WnT4R13PR71O125O0nrlPKzW3341GbiCRGYDMQe22kUdXKhxF+HRu1V0Yc7UFHq6h97zP3zaSK2R/FgzYKK1wIDAQABAoIBAAQdAmOGBgWbkSeaD4dmBtA/d4jsLlZUNwP/HRRzDNEYlwqweMoAj534WYyzp4DC+b2FIuf64tNrK1VdfvXL3bIuxYU3cSnS5HNgOE82x/MmMuK6p5Fyauv8Ed1MHAGw2xBKVGqrFNNQbG1S1O8By0BoGkvDRWyRX2ljSwHkiba1J9SETDEpq8yIPA2mvB+gq+0AgB0UdiQvDksZBApm10csqFp4vGL6tn+4p8VQ3QrAbkKRLE2EWde2lcJ+x0Q2u9KFftumWmnug6rlqQ5r87vS6j0LEx9nXzVWUmxTfp07gZKWuo37DVWYK3n/c46r14NAUJGSEIpSv4qehZmeipECgYEAvl5eSiUzMQykOCWOC/d1ShDnWlqZ8T4Icl8v94HWQwKXmCEQemcManFnf4od2QAq/FGHwEwzatbV7kUHsAZBwwkl9QTv//dWHUNTb64H/Enp4nl6uYWSpstdupf4EmmRzymxoxe+0OH3MdUTISKzu7cVIOxNq4S04ouF1rzYIZUCgYEAsjoAlVK9/b3QQqme3j1grkCJYxWpy0y+MvM4lEkQyGUu+g43f/AehtMdd/k+IffOASAB27EeLq3JM4xhCwt+c1Rk7I+Ahpd6GwAuCZq5OxbYuryXvdz9L1Imrg0ZNWI4FPQd/NKv5uUmVg62XF2FPale9LGJgHsMLS0u2NX/N7sCgYBku9zbETo4bz8KstyqemRnL/CsQBsLq86ebr4cE2lEhj+fcYedrZ/FR4MD2xsWM9+LKr1RxUDD/TCw53g87eKoiNO8BsPUx5Wa4IBrLUTufFI11CBbwVGrzxsm3LmZTKGqZJ6p9au0Lo42oVCBDTLcVvHoPQKQiyWIZ1oIGl+nzQKBgQCFEoO++pG7NI+cRpPFMiTO1ob+QX2OuxVEa4/yP//U8J+7uwO7dekFF/pnyuSWbjUVZ/WbOErl5YJWB9mpaoKW0AuvCZKYa9+S63Z0W180fGGERC40aB1uyLLyv/rzfguLsZ32WMVX6/7MwJ1up7FFkgHBSBH3qWg1DwhshJPT4wKBgE+VzRX3I3M/sMwbGGWiLUC7BfJq8iBczFee3VmiKKbeo97Fn7eKJ4prdP5+RLObWeuUHUnRWQdLmirBpSeeKMZ1ZgVJG8VZVO7hlKx3u1vPdrnfdfw02vOBJ5cPJ3HGwLhH0ZWtw6dUhDjeHdUZjnsFScbOrgywmosnS7wYMSti';
                $this->aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkONhL/otLyTVbWdcutt1zpeUqGYr5l0B6/vbyhiJFBsrRnKVrI21zSZtylHutpc+z0InCLeDgQVNy2UVhHuhcfe/Trg1Eq5/a1+Fs7yK6OXJB0lTbPt8L37ACbqpUphooTmd2scsgPiPkOT42EoX4mQxBo1a5V/wgAmKfTz5SLq4b2PYtepMtmGdMWrXV/l9x15bMcaxgavf75NgKzVXxgKMCiQjIyJylSqkCIe7Nwn+96QAuks+NeJXpTWuFYF1q5XfaKvnym6XDXiUIOHc4PS+OZHEzKtYaZl1SCRVwK2lOqOlwNSlUsBZ7NBMng6jT7YbsfiUyJcFj9Egob7JVQIDAQAB';
                $this->notifyUrl = 'https://api.xykj1.com/goods-order/zfb-async?pay_version=ios_v2';

            }else{
                //xijin
                $this->aop->appId = '2021001107613197';
                $this->aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
                $this->aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAlRQH8NjkOlsJM93TJ/AGwk/7Y15JPewuO3lLmjb8wibAqlTSe4uA6mHlPAL+u2EJvo3X6lvhTb2+qbIC1Kgwoi8NtCUG+GW+8XXiUlCqrwwhfUVu7amIQCuFKbfETNrn8hsv0xy0JlacmuMNHk1mphZg8NnyOKO1EZYyo0PgwZ5QwkzDFOYgMjB46zwdmSxUEvaw42lmzw+fS+NbhWmEWt7sHweZKUGGuc3wbzIqp2adi4moVFhFSz86HRtthzs+ycSzkLDUOTSF9jnffCCuioFbt2t5dV0DQLlbnV+Ycfu03qtqxmwul+Nn+Wx2N76D6+ncKxZ74uSzOD/VyNloNQIDAQABAoIBAQCMheU2UjxQBaAXE/IGo2q7n0kH0ciYZhjuVte89jgrivvxkyMU8dsDFGRQvUIAvufAh1QPxawK6+DddL7WVfQB7Pit5nk7jC0Azm4XyZIajwTCYMC/ph6Y3m0XdfHDwFUDEj1ycowd12anWX70IWGxDUczwT1hxm7XtQzmeQVTe8dkLDD1ASZ915ONjoyx7qX5QvW4FOzPi9gv/V9RXtmmpX2VkyiDa5nBIJOnOmVqOLydyRrPVMoU5GON/6jzMrle4DcrATZdJia6GdoaxckpCDrS6httR7N+5U1zTev63/eK4emRnGcIrMQDfN10gyfXy4cx9B3JVu65w7W2DvoBAoGBAMvM7ipizQlDNzuuojabBUoSmIyzk12bqEAFnbUIRiTiTAKQOsx7HKT50T6Drn7dz0iQCs6NPI1I45vudUzzEpgFrHsOFX9+QUqtjBqfSXPSNbMzkejKWciQaFzRrub06Py0jUUPVyCTMZQXlwm86s9kWxK3AInKmAuZVKElkfqdAoGBALtC/ynlio30FkZMkl4SqOVeaotgZJHExdLLH1RX4gSLm1C/VN0HtC03sH3nvjtAFYvr4+3EQkTMhntL4C5KtjmOezALBprz/G6JMLO8rwhmZ31wASfPiH9JPlAMfQugKi5W92hFFK/6Ztn7KH+zx5Yu5S9whlUH+BVq3Cgh1YR5AoGATneTx4wM56ESlg8R87ZSHgf05k+J0MPKzwOaigYqg68BlwcTLIHGMJri/o/S0pPyaDzVfFuS0c8bm4D/duYr5Y5hr2tnyZPB7c/OHr3vmQF5nYqghiHK2dcH55zwG4p2Xj3iGpHieO9TanJ+u8gaHEnGavk84tC3I9ggZn9Bql0CgYBZIz8K1nTptXKU7AYQOZmj9RmKVswIhz4TZRT4tQylVT8NZMYkoHInxAaDKDjpQ0v+Wr8Ygv94eFLbhfakknPDWgtRhy4RvGpAw5UnOX2n5fcFQreKF8PFszuyJv+u9QKfJE6Il7mNOwiSYneYsh8jYhc+SbXn6+8vYrqBb/mPIQKBgQCVdUnuTK/xtalYZq9VPrueeg4sPfeWUNXbmtBqXXERoBSW7yenvb3nxGYkvRT3ymLrCPHQkq8puWIDoWK764HGzSeQ3rjrge7/s8uw6lqqQRea0HJ5xx6M25Bg/G6bPh1RALoZL5k9qNZOQU+wI2E/HnUJYHcZSU72nHnMIbhKeA==';
                $this->aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkONhL/otLyTVbWdcutt1zpeUqGYr5l0B6/vbyhiJFBsrRnKVrI21zSZtylHutpc+z0InCLeDgQVNy2UVhHuhcfe/Trg1Eq5/a1+Fs7yK6OXJB0lTbPt8L37ACbqpUphooTmd2scsgPiPkOT42EoX4mQxBo1a5V/wgAmKfTz5SLq4b2PYtepMtmGdMWrXV/l9x15bMcaxgavf75NgKzVXxgKMCiQjIyJylSqkCIe7Nwn+96QAuks+NeJXpTWuFYF1q5XfaKvnym6XDXiUIOHc4PS+OZHEzKtYaZl1SCRVwK2lOqOlwNSlUsBZ7NBMng6jT7YbsfiUyJcFj9Egob7JVQIDAQAB';
                $this->notifyUrl = 'https://api.xykj1.com/goods-order/zfb-async';
            }

        }else{
            if($pay_version == 'xijing_v3'){  // 无语
                $this->aop->appId = '2021001164675795';
                $this->aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
                $this->aop->rsaPrivateKey = 'MIIEogIBAAKCAQEAwcuVn5+7d7KsIEtZJGCsdxOeyvAGd8ljd20JxAcXyXi9Oqp4m37cHVIysFVngf535x/hGwEKWZpVdCKQ0h8mr7nk1RcVXylmYaj4/SrU73RBd9Z2Hy5BEPHWK3vdu867DhMTW2cNLtcA1EXOYBX4+CILl4/UzseSeqxYfBFkiBoPfYRg8rJrFw0yvFlPVnf4QWCojohA2uaOz4db+wCe4e78ah8Occ/xVaCxbzW0acJcYsSLvQU3/G5/1gazXtX88q3qThTw6yOOGRhJ3UAkfd4QgI4iuDw92G9uTrV5d5ZzQEYCNlYIQuBjBBeScAEKfH7wsbRX94SFs4gLZozylQIDAQABAoIBAFj9MVHBOYZ3h061CtQHiGqNmec6XN34njIi4c8gKq4bQVmFGijdEhpsulX6gbNRtKFvJCEQUz5d/kt76+Bw6YTb6dZn6SpvilHiE6O9XVYvnjh3RHqA9OPhCSWilWk+Purxm3vnUEhn9juDaob40ACxMX2BUIk7X2nxZhhirDVKZB+sjikuABTBB/HplrUkBbnJ5xTXmSv/nnAZAxe8/MXTMMkp6v4R6c6XwklhknA/gpfZHmR0lKPyy7WIhxsdMMEIP3ostfaxbwSc1HCXLLotb1e+hT87jdmHik6xWN3JKBRM+m86GMhGFJL0p42/Mo+cO+APTxHSY1ufYuy/P8ECgYEA+/+XwgWK+FXoYLO8mhT4dxXfjSVJg2+IHvmR+SRrUOCD+g1210P8tUCUqX8Zj3nyyvvHpnVCRAX/iY6l9ng4/IFPq7zRB2MK2IFEMKY1CD9/WmyDsNWsWzNA+Bm36TNL+AgLwy6aXqFELNNwQ+iyGNRGrclKpe4FjCMw9J7PIj0CgYEAxN9jV2eL/IoLjLaT48xWfri8QU9/gni3uCDBAkOWTdzOOdboupcc9GI0zfa2WtqHDMWIlxuJGSY6kFT8jsw0vrfloeNI+yVBCJEcCf40fhwnm+sxYUg1xfxLifl7Ds1veDIlDu6Dxg61o7I73PGnCVmPbNfiARsGPO/HX1urzzkCgYA4QhR4PVKMJGmvhRDa+UmLFHgHA8cIr9Kcz6y2CVpoAOeV4IGih0wYjuVhfb2QGXKJvHITCGSV2Rz2ccE9aBOY06mctpeX5ZWbRiiaD9ERGVPuNQGlbd5/sc6UVPGI/2m7Yth1Z6cQ70HQHBgZIQ75mhJ5Y9Hlf2D/qy+XvQiTJQKBgChOvF4m2yhncxnqP7lCGutzE0gj6K1j3ema+yllgR6L+MUq1Rdu3QTEHp+UxZ0wZLoer2qQvq8hfpgSbmsmMB5kNGXCzSxuYjRI0X7SRxkS1qE7tC6AxFG26BhlteY/9XScf63g86XXWSUc1tjTuSlW5uCYCrPDPrG3PhCuCi0xAoGARriBoVt2XKOKV3bzC5wSW1c8ot0uZIn3tHzVcUrOzpTRup6gLuLj3TzhyZJLJp+36NM6GOYpqcVr18KgRAazUYQdPnP8yW4KQ8nzG+ONjK3Rx0+sfampXFP+7vfjYV6tSJDlxxMBdTP00FYbuzx4rAIwtjxmMcUXyJQYhbaUi6g=';
                $this->aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwcuVn5+7d7KsIEtZJGCsdxOeyvAGd8ljd20JxAcXyXi9Oqp4m37cHVIysFVngf535x/hGwEKWZpVdCKQ0h8mr7nk1RcVXylmYaj4/SrU73RBd9Z2Hy5BEPHWK3vdu867DhMTW2cNLtcA1EXOYBX4+CILl4/UzseSeqxYfBFkiBoPfYRg8rJrFw0yvFlPVnf4QWCojohA2uaOz4db+wCe4e78ah8Occ/xVaCxbzW0acJcYsSLvQU3/G5/1gazXtX88q3qThTw6yOOGRhJ3UAkfd4QgI4iuDw92G9uTrV5d5ZzQEYCNlYIQuBjBBeScAEKfH7wsbRX94SFs4gLZozylQIDAQAB';
                $this->notifyUrl = 'http://47.103.61.179:1080/goods-order/zfb-async?pay_version=xijing_v3';

            }
            if($pay_version == 'ios_v2'){
                $this->aop->appId = '2021001164691116';
                $this->aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
                $this->aop->rsaPrivateKey = 'MIIEowIBAAKCAQEAhIi/X/UeaWtUOMpGKXpc62TppVhCxSq1xNykkgOu9t0HNY4oFVCAsjbQjqAewyTQfLZMqeKmbP6VluKW8YERoGer7HymS4XzMi1FJub5ckzHesgnyN8SLK3rh+0IiIqKlzUzunY+um44mvd5TyHDXEzc/m9o8oHipPc9UT+ixLV842ZDFP+m4yC6+9RH5GKgLL4onmbhH3Ee7B9wSB5h/86REXanfRByNA0VdtpjotMbhht3Eswbaf43Wahi1xmT6WnT4R13PR71O125O0nrlPKzW3341GbiCRGYDMQe22kUdXKhxF+HRu1V0Yc7UFHq6h97zP3zaSK2R/FgzYKK1wIDAQABAoIBAAQdAmOGBgWbkSeaD4dmBtA/d4jsLlZUNwP/HRRzDNEYlwqweMoAj534WYyzp4DC+b2FIuf64tNrK1VdfvXL3bIuxYU3cSnS5HNgOE82x/MmMuK6p5Fyauv8Ed1MHAGw2xBKVGqrFNNQbG1S1O8By0BoGkvDRWyRX2ljSwHkiba1J9SETDEpq8yIPA2mvB+gq+0AgB0UdiQvDksZBApm10csqFp4vGL6tn+4p8VQ3QrAbkKRLE2EWde2lcJ+x0Q2u9KFftumWmnug6rlqQ5r87vS6j0LEx9nXzVWUmxTfp07gZKWuo37DVWYK3n/c46r14NAUJGSEIpSv4qehZmeipECgYEAvl5eSiUzMQykOCWOC/d1ShDnWlqZ8T4Icl8v94HWQwKXmCEQemcManFnf4od2QAq/FGHwEwzatbV7kUHsAZBwwkl9QTv//dWHUNTb64H/Enp4nl6uYWSpstdupf4EmmRzymxoxe+0OH3MdUTISKzu7cVIOxNq4S04ouF1rzYIZUCgYEAsjoAlVK9/b3QQqme3j1grkCJYxWpy0y+MvM4lEkQyGUu+g43f/AehtMdd/k+IffOASAB27EeLq3JM4xhCwt+c1Rk7I+Ahpd6GwAuCZq5OxbYuryXvdz9L1Imrg0ZNWI4FPQd/NKv5uUmVg62XF2FPale9LGJgHsMLS0u2NX/N7sCgYBku9zbETo4bz8KstyqemRnL/CsQBsLq86ebr4cE2lEhj+fcYedrZ/FR4MD2xsWM9+LKr1RxUDD/TCw53g87eKoiNO8BsPUx5Wa4IBrLUTufFI11CBbwVGrzxsm3LmZTKGqZJ6p9au0Lo42oVCBDTLcVvHoPQKQiyWIZ1oIGl+nzQKBgQCFEoO++pG7NI+cRpPFMiTO1ob+QX2OuxVEa4/yP//U8J+7uwO7dekFF/pnyuSWbjUVZ/WbOErl5YJWB9mpaoKW0AuvCZKYa9+S63Z0W180fGGERC40aB1uyLLyv/rzfguLsZ32WMVX6/7MwJ1up7FFkgHBSBH3qWg1DwhshJPT4wKBgE+VzRX3I3M/sMwbGGWiLUC7BfJq8iBczFee3VmiKKbeo97Fn7eKJ4prdP5+RLObWeuUHUnRWQdLmirBpSeeKMZ1ZgVJG8VZVO7hlKx3u1vPdrnfdfw02vOBJ5cPJ3HGwLhH0ZWtw6dUhDjeHdUZjnsFScbOrgywmosnS7wYMSti';
                $this->aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkONhL/otLyTVbWdcutt1zpeUqGYr5l0B6/vbyhiJFBsrRnKVrI21zSZtylHutpc+z0InCLeDgQVNy2UVhHuhcfe/Trg1Eq5/a1+Fs7yK6OXJB0lTbPt8L37ACbqpUphooTmd2scsgPiPkOT42EoX4mQxBo1a5V/wgAmKfTz5SLq4b2PYtepMtmGdMWrXV/l9x15bMcaxgavf75NgKzVXxgKMCiQjIyJylSqkCIe7Nwn+96QAuks+NeJXpTWuFYF1q5XfaKvnym6XDXiUIOHc4PS+OZHEzKtYaZl1SCRVwK2lOqOlwNSlUsBZ7NBMng6jT7YbsfiUyJcFj9Egob7JVQIDAQAB';
                $this->notifyUrl = 'http://47.103.61.179:1080/goods-order/zfb-async?pay_version=ios_v2';
            }else{  // 沙箱环境
                $this->aop->appId = '2016102100732339';
                $this->aop->gatewayUrl = "https://openapi.alipaydev.com/gateway.do";
                $this->aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAk3wGFQNVTNvwkdF9ney5MFlEbpdOgrezi8djPAl/3ES/fbKIIyEuFRG0Li9rcYMdg3nQgmrtxJ0SFkvVRuT8/xqFHGAQTWLxPcfJVjPyHqYJkVYHwM47pfOhj00HXZCeTOOifnecL2LIXe+XkQDUsBhd+ln7l0ELCUMblyK37RiOO8mO9neFj00mmaKlRbhs0EdqkTbHUGu9eUuCCz4n1ctiENpklELxRDgBIuWPFeJoc7HcPC2S6ZeRgI43sWAeNNjwZoJsMdC4MdC+Bp17Saj568Ul5qgV6/8h0OIx5qdu/UcCDM4zYNA1ez7Xb3aaIUoipiFpXY5VhhXARc7W7QIDAQABAoIBABlLZB+merK75f6cV1cGipxoMvxkpup0Zy7J+5MIbg1xHQaJ4B9mSWkDqEtjhqHpQt6RvdkgTbcy2S9JwkJuc5WtDrdXInSjS4y0/yrxrczj3TA3+QxwUnqb1lGlfGXnr76wK+ZUzUyzPHof5XbRFak+BKAzXpsKZMIp6El7gt/+2sE14f29k6Mq6eBLUZA9qYhck9UP84/bVVAt3GBzMIjRYKaqlRBCN+7DBWgi/Q1xPJMXrCsGWvbDrygPoI29Lfw9Wg5ZVqs3QHboJC7PIcS9Wx25el/0goEKa0u5JdvP0XgGxAtl6XuX5jVi9VjGKdjHbDPagFlN6OrylcTT7gECgYEA4Y9/S30RTr+gRfFrLVsIUmhtUbkb2WLsQpFmStZCjGYdRsRP1aGu+uhYWYuvRsXhf16upNcF2nKooqMW+09iiGIao9uuwm45gJWPz4015St1S/Y2MtkuAdN92ZqU2DAiNRS9ZJQqN+T2WF8wxeoX6YNrZyF+U8jkbr4f/NfXGVECgYEAp2M19zq66KCy9SxhVWYPLKvbpDHYxjYKkWtgSI4XdltpYaExjoS5T298EzPmikLsdRmnOBAB3hCQalDUgvTmitvHFW6B6553yz1PcAwBWv6zOjAfzR7+fI5Hyc2dTZMLBxOIbd24deRn8ZQaNsnMrtGWdx24ToZPHLHVUHh/PN0CgYEAtv1jmCpu0CRGMngZK0KfDbwKdDJolYPbiGXNRwlsE9sEBVVeL0JsYsH8aykXmaMPvfViWfjXltFOXiNRkbBlj5+HXUfsD3C81cSAVzGId9M55dZpQfdPi8u4XcE63+is18Nrox1Q4uetsyQGvYmcvKURjSlvAyoHqJA2AzpYFwECgYAzGi7Xk66MZsM75piwob/wO2eK8Uzk9oqzaryyae7vy2iYzcV7ZTnN7pNKqYWwoE3gUjyi+QAY86WjT5oRgQMBUaet+1bewDqCae1en8uqJChDk32h+dxn2mhAS+3CSSoMtwHnyNEP7PNu5XA7WdCLbqEpLIGgN5Dfics+DguKYQKBgQC6zZK55WIvsOXW7q0HmLS5vfw8gBDFuaWxFIKb6vZ3c5BnSrWPdrgV+UQjyhledhqdfZjTQA2QsWZZHW7W4fTE5PddJUENhSlKUKvV2cjlAVfEzxP6Op2VPT/MI9KxI0Qjzv16V2ASMuimxzKCgpTdO812pJga30SIrCV53SnDNw==';
                $this->aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAgxQHbDbOLYpmSmRI8xSV0cSyE9ecI/OamP/biVmVVa6pURp46t00ruuPL7IrTQ+w4hVfvIcgTTjDRWuyVD0+JmKnskmwjwnNeESDMzvIOEH91xj3MIAsIJodnCoQ2TJftqy1fzvC1b77NzGduQMfyMvxmFvVVff6mgjwiHxxhhcGsL1SrMe9hh1bjFDx1C3hhJvf9+dcVGelTmlTmN9mHJjAlPAiPToOYHVRZzQctaZU0hatv9bECHpmv7zIPf9oBI9X+goibgP+cGYInIKamoJkWftIekMC7bHSPqM/WL9r4ep/Sudqr4OTTh9EvhmGdqXwTP0YoVaOJtBHAqsfuwIDAQAB';
                $this->notifyUrl = 'http://47.103.61.179:1080/goods-order/zfb-async';  // 回调地址
            }
        }
    }

    /*
     * 创建支付订单
     * */
    public function createOrder($params, $type){
        $bizcontent = "{\"subject\": \"@goods_name@\","
            . "\"out_trade_no\": \"@order_sign@\","
            . "\"total_amount\": \"@total_amount@\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\","
            . "\"passback_params\":\"@type@\""
            . "}";
        $bizcontent = str_replace('@goods_name@',$params['subject'],$bizcontent);
        $bizcontent = str_replace('@order_sign@',$params['order_sign'],$bizcontent);
        $bizcontent = str_replace('@total_amount@',$params['price_total'],$bizcontent);
        $bizcontent = str_replace('@type@',$type,$bizcontent);
        $request = new \common\aop\AlipayTradeAppPayRequest();
        $request->setNotifyUrl($this->notifyUrl);
        $request->setBizContent($bizcontent);
        $str = $this->aop->sdkExecute($request);
        return $str;
    }

    /*
     * 退款
     * */
    public function refund($order_sign, $price){
        $request = new \common\aop\AlipayTradeRefundRequest();
        $bizcontent = "{" .
            "\"out_trade_no\":\"@order_sign@\"," .
            "\"refund_amount\":@refund_amount@".
            "}";
        $bizcontent = str_replace('@order_sign@',$order_sign,$bizcontent);
        $bizcontent = str_replace('@refund_amount@',$price,$bizcontent);
        $request->setBizContent($bizcontent);
        $result = $this->aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000) return true; // 退款成功
        return false; // 退款失败
    }

    /*
     * 验签
     * */
    public function checkSign($data){
        $flag = $this->aop->rsaCheckV1($data, NULL, "RSA2");
        return $flag === true ?: false;
    }

}