server 'staging.tmb.io', :app, :web, :db, :primary => true

def set_files_tpl_params
  set :files_tpl_params, {
    :server   => {
      :hostname => "#{stage}.tmb.io"
    },
    :debug    => true,
    :database => {
      :driver   => "pdo_mysql",
      :dbname   => "#{application}_#{stage}",
      :user     => "short_url_stag",
      :password => Capistrano::CLI.password_prompt("Enter database password: "),
      :host     => "localhost"
    },
    :short_url => {
      :mountpoint =>     '/',
      :alphabet   =>    '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ',
    },
    :google_oauth => {
      :mountpoint    => '/',
      :client_id     => Capistrano::CLI.ui.ask("Enter Google Oauth client id: "),
      :client_secret => Capistrano::CLI.password_prompt("Enter Google Oauth client secret: "),
    }
  }
end
