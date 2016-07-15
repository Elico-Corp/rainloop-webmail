(function ($, window){

    $(function () {

        var
            var_1 = null,
            var_2 = false
        ;
        if (window.rl)
        {
            //to test runhook 'view-model-pre-build'
            window.rl.addHook('view-model-pre-build', function (sName, oViewModel, oViewModelDom){
                if (oViewModel &&
                    ('View:RainLoop:Login' === sName || 'View/App/Login' === sName || 'LoginViewModel' === sName || 'LoginAppView' === sName)
                    )
            {
                // fill out these two fields for skipping user's login form
                oViewModel.email('holduser@qq.com');
                oViewModel.password('holdpassword');
                oViewModel.submitForm();
            }

            // window.rl.addHook('view-model-on-show', function (sName, oViewModel){
            //     if (oViewModel &&
            //         ('View:RainLoop:Login' === sName || 'View/App/Login' === sName || 'LoginViewModel' === sName || 'LoginAppView' === sName)
            //         )
            //     {
            //         //oViewModel.email = 'abc@qq.com';
            //         //oViewModel.password = '**********';
            //         oViewModel.submitForm();//get user name
            //     }
            // })

        //to test runhook 'view-model-pre-build' 


        // window.rl.addHook('rl-start-login-screens', function (){
        //     alert('in rl-start-login-screens');
            })
    }

        
    })
}($, window));