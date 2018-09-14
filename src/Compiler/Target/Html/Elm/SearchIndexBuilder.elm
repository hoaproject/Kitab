port module SearchIndexBuilder exposing (Message(..), Model, SearchIndex, exportIndex, index, input, main, output, update)

import ElmTextSearch exposing (..)
import Platform


type alias SearchIndex =
    { id : String
    , normalizedName : String
    , description : String
    }


port input : (List SearchIndex -> msg) -> Sub msg


port output : String -> Cmd msg


type alias InitInput =
    ()


type alias Model =
    ()


type Message
    = Export (List SearchIndex)


init : InitInput -> ( Model, Cmd Message )
init _ =
    ( (), Cmd.none )


update : Message -> Model -> ( Model, Cmd Message )
update message model =
    case message of
        Export database ->
            ( model, output (exportIndex database) )


index : ElmTextSearch.Index SearchIndex
index =
    ElmTextSearch.new
        { ref = .id
        , fields =
            [ ( .normalizedName, 3.0 )
            , ( .description, 1.0 )
            ]
        , listFields = []
        }


exportIndex : List SearchIndex -> String
exportIndex database =
    ElmTextSearch.storeToString (Tuple.first (ElmTextSearch.addDocs database index))


main =
    Platform.worker
        { init = init
        , update = update
        , subscriptions = \model -> input Export
        }
