port module SearchIndexBuilder exposing (..)

import Platform
import ElmTextSearch exposing (..)

type alias SearchIndex =
    { id: String
    , normalizedName: String
    , description: String
    }

port input: (List SearchIndex -> msg) -> Sub msg
port output: String -> Cmd msg

type alias Model = ()

type Message =
    Export (List SearchIndex)
        
update: Message -> Model -> (Model, Cmd Message)
update message model =
    case message of
        Export database ->
            (model, output (exportIndex database))

index: ElmTextSearch.Index SearchIndex
index =
    ElmTextSearch.new
        { ref = .id
        , fields =
            [ (.normalizedName, 3.0)
            , (.description, 1.0)
            ]
        , listFields = []
        }

exportIndex: List SearchIndex -> String
exportIndex database =
    ElmTextSearch.storeToString (Tuple.first (ElmTextSearch.addDocs database index))

main =
    Platform.program { init = ((), Cmd.none), subscriptions = \model -> input Export, update = update }
